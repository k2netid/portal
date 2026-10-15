<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberGoogleAuthService;
use Modules\Member\Support\MemberPublicProfile;
use PragmaRX\Google2FA\Google2FA;

class GoogleAuthController extends BaseApiController
{
    public function __construct(
        private MemberGoogleAuthService $googleAuth,
    ) {}

    public function redirect(Request $request): RedirectResponse|JsonResponse
    {
        $this->googleAuth->applyRuntimeConfig();

        if (! $this->googleAuth->isAvailable()) {
            return $this->error('Google login is not available.', 503, [], 'MEMBER_GOOGLE_UNAVAILABLE');
        }

        $redirect = is_string($request->query('redirect')) ? (string) $request->query('redirect') : '/member';
        $state = $this->googleAuth->storeOAuthState($redirect);

        /** @var GoogleProvider $driver */
        $driver = Socialite::driver('google');

        return $driver->stateless()
            ->scopes(['openid', 'profile', 'email'])
            ->with(['state' => $state, 'prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $this->googleAuth->applyRuntimeConfig();

        $stateKey = is_string($request->query('state')) ? (string) $request->query('state') : null;
        $state = $this->googleAuth->pullOAuthState($stateKey);
        $redirectPath = $state['redirect'] ?? '/member';
        $ip = IpHelper::getClientIp($request);

        if ($state === null) {
            return redirect()->away($this->googleAuth->frontendLoginUrl([
                'oauth_error' => 'invalid_state',
            ]));
        }

        if (! $this->googleAuth->isAvailable()) {
            return redirect()->away($this->googleAuth->frontendLoginUrl([
                'oauth_error' => 'unavailable',
            ]));
        }

        try {
            /** @var GoogleProvider $driver */
            $driver = Socialite::driver('google');
            $googleUser = $driver->stateless()->user();
            $resolved = $this->googleAuth->resolveMember($googleUser);
            $member = $resolved['member'];
        } catch (\RuntimeException $e) {
            $this->googleAuth->audit('member_google_login_failed', null, $e->getMessage(), $ip);

            return redirect()->away($this->googleAuth->frontendLoginUrl([
                'oauth_error' => 'registration_disabled',
            ]));
        } catch (\Throwable $e) {
            Log::warning('Member Google OAuth callback failed', ['error' => $e->getMessage()]);
            $this->googleAuth->audit('member_google_login_failed', null, 'Google OAuth callback failed', $ip);

            return redirect()->away($this->googleAuth->frontendLoginUrl([
                'oauth_error' => 'failed',
            ]));
        }

        if ($member->status !== 'active') {
            $this->googleAuth->audit('member_google_login_failed', $member, 'Inactive member Google login', $ip);

            return redirect()->away($this->googleAuth->frontendLoginUrl([
                'oauth_error' => 'inactive',
            ]));
        }

        if ($member->hasTwoFactorEnabled()) {
            $pending = $this->googleAuth->storePendingTwoFactor($member);
            $this->googleAuth->audit(
                'member_google_login_2fa_required',
                $member,
                "Google login requires 2FA for: {$member->email}",
                $ip,
            );

            return redirect()->away($this->googleAuth->frontendLoginUrl([
                'google_2fa' => '1',
                'pending' => $pending,
                'redirect' => $redirectPath,
            ]));
        }

        $token = $this->googleAuth->issueToken($member);
        $this->googleAuth->audit(
            $resolved['created'] ? 'member_google_register' : 'member_google_login_success',
            $member,
            ($resolved['created'] ? 'Member registered via Google: ' : 'Successful Google member login: ').$member->email,
            $ip,
        );

        return redirect()->away($this->googleAuth->frontendCallbackUrl([
            'token' => $token,
            'redirect' => $redirectPath,
        ]));
    }

    public function completeTwoFactor(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'pending' => 'required|string',
                'two_factor_code' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $pending = (string) $validated['pending'];
        $code = trim((string) $validated['two_factor_code']);
        $ip = IpHelper::getClientIp($request);

        $member = $this->googleAuth->pullPendingTwoFactorMember($pending);
        if (! $member instanceof Member) {
            return $this->error('Google login session expired. Please try again.', 422, [], 'MEMBER_GOOGLE_2FA_EXPIRED');
        }

        if (! $this->verifyMemberTwoFactor($member, $code)) {
            $this->googleAuth->audit(
                'member_google_login_failed',
                $member,
                "Invalid Google login 2FA for: {$member->email}",
                $ip,
            );

            return $this->validationError([
                'two_factor_code' => ['Invalid two-factor authentication code'],
            ]);
        }

        $this->googleAuth->forgetPendingTwoFactor($pending);
        $token = $this->googleAuth->issueToken($member);
        $this->googleAuth->audit(
            'member_google_login_success',
            $member,
            "Successful Google member login (2FA): {$member->email}",
            $ip,
        );

        return $this->success([
            'member' => $member->fresh()?->toPublicProfile() ?? MemberPublicProfile::serialize($member),
            'token' => $token,
        ], 'Member logged in');
    }

    private function verifyMemberTwoFactor(Member $member, string $code): bool
    {
        $twoFactor = $member->twoFactor;
        if ($twoFactor === null || ! $twoFactor->enabled) {
            return false;
        }

        $secret = $twoFactor->getDecryptedSecret();
        if ($secret && (new Google2FA)->verifyKey($secret, $code, 2)) {
            return true;
        }

        return false;
    }
}
