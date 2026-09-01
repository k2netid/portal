<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Core\System\Contracts\LoginThrottlePortInterface;
use Modules\Core\System\Contracts\PasswordPolicyPortInterface;
use Modules\Core\System\Helpers\IpHelper;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Setting;
use Modules\Member\Contracts\MemberSecurityAuditPortInterface;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberEmailVerification;
use Modules\Member\Support\MemberCaptchaGuard;
use Modules\Member\Support\MemberPublicProfile;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends BaseApiController
{
    public function register(Request $request): JsonResponse
    {
        if (! (bool) Setting::get('enable_member_registration', true)) {
            return $this->error('Member registration is currently disabled.', 403, [], 'MEMBER_REGISTRATION_DISABLED');
        }

        try {
            MemberCaptchaGuard::assert($request, 'register');
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:mem_members,email',
                'password' => ['required', 'string', 'confirmed', app(PasswordPolicyPortInterface::class)->rule()],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $member = Member::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 'active',
        ]);

        $token = $member->createToken('member')->plainTextToken;

        try {
            app(MemberEmailVerification::class)->send($member);
        } catch (\Throwable) {
            // Registration still succeeds if outbound mail is not configured.
        }

        app(MemberSecurityAuditPortInterface::class)->record(
            'member_register',
            $member,
            "Member registered: {$member->email}",
        );

        return $this->success([
            'member' => $member->toPublicProfile(),
            'token' => $token,
        ], 'Member registered', 201);
    }

    public function login(Request $request): JsonResponse
    {
        try {
            if (! $request->filled('two_factor_code')) {
                MemberCaptchaGuard::assert($request, 'login');
            }
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
                'two_factor_code' => 'sometimes|nullable|string',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $email = trim((string) $validated['email']);
        $password = (string) $validated['password'];
        $ipAddress = IpHelper::getClientIp($request);
        $throttle = app(LoginThrottlePortInterface::class);
        $audit = app(MemberSecurityAuditPortInterface::class);

        if ($blocked = $throttle->blockedState('member', $email, $ipAddress)) {
            $audit->record(
                'member_login_throttled',
                null,
                "Member login throttled for email: {$email}",
                ['email' => $email, 'retry_after' => $blocked['retry_after']],
                $ipAddress,
            );

            return response()->json([
                'success' => false,
                'message' => $blocked['message'],
                'retry_after' => $blocked['retry_after'],
                'error_code' => 'MEMBER_LOGIN_THROTTLED',
            ], 429);
        }

        $member = Member::query()->where('email', $email)->first();
        if ($member === null || ! Hash::check($password, (string) $member->password)) {
            $result = $throttle->recordFailure('member', $email, $ipAddress);
            $audit->record(
                'member_login_failed',
                $member,
                "Failed member login for email: {$email}",
                ['email' => $email],
                $ipAddress,
            );

            if ($result['blocked']) {
                $audit->record(
                    'member_login_throttled',
                    $member,
                    "Member login locked after failures for email: {$email}",
                    ['email' => $email, 'retry_after' => $result['retry_after']],
                    $ipAddress,
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed attempts. Please try again later.',
                    'retry_after' => $result['retry_after'],
                    'error_code' => 'MEMBER_LOGIN_THROTTLED',
                ], 429);
            }

            return $this->error('Invalid member credentials', 401);
        }

        if ($member->status !== 'active') {
            $audit->record(
                'member_login_failed',
                $member,
                "Inactive member login attempt: {$email}",
                ['email' => $email, 'reason' => 'inactive'],
                $ipAddress,
            );

            return $this->error('Member account is not active', 403);
        }

        if ($member->hasTwoFactorEnabled()) {
            $code = isset($validated['two_factor_code']) && is_string($validated['two_factor_code'])
                ? trim($validated['two_factor_code'])
                : '';

            if ($code === '') {
                return $this->success([
                    'requires_two_factor' => true,
                    'member' => [
                        'email' => $member->email,
                    ],
                ], 'Two-factor authentication required');
            }

            if (! $this->verifyMemberTwoFactor($member, $code)) {
                $throttle->recordFailure('member', $email, $ipAddress);
                $audit->record(
                    'member_login_failed',
                    $member,
                    "Invalid member 2FA code for email: {$email}",
                    ['email' => $email, 'reason' => 'invalid_2fa'],
                    $ipAddress,
                );

                return $this->validationError([
                    'two_factor_code' => ['Invalid two-factor authentication code'],
                ]);
            }
        }

        $throttle->recordSuccess('member', $email, $ipAddress);
        $member->forceFill(['last_login_at' => now()])->save();

        $token = $member->createToken('member')->plainTextToken;

        $audit->record(
            'member_login_success',
            $member,
            "Successful member login for: {$member->email}",
            [],
            $ipAddress,
        );

        return $this->success([
            'member' => $member->fresh()?->toPublicProfile() ?? MemberPublicProfile::serialize($member),
            'token' => $token,
        ], 'Member logged in');
    }

    public function me(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        return $this->success($member->toPublicProfile(), 'Member profile');
    }

    public function logout(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if ($member instanceof Member) {
            app(MemberSecurityAuditPortInterface::class)->record(
                'member_logout',
                $member,
                "Member logged out: {$member->email}",
            );
            $member->currentAccessToken()?->delete();
        }

        return $this->success(null, 'Member logged out');
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse|RedirectResponse
    {
        $verification = app(MemberEmailVerification::class);
        $member = Member::query()->find($id);
        $status = 'invalid';

        if ($member instanceof Member && $verification->isValidHash($member, $hash)) {
            if ($member->email_verified_at !== null) {
                $status = 'already';
            } else {
                $member->forceFill(['email_verified_at' => now()])->save();
                $status = 'ok';
            }
        }

        if ($request->expectsJson()) {
            if ($status === 'invalid') {
                return $this->error('Invalid verification link', 403);
            }

            return $this->success(['email_verified' => true], $status === 'already' ? 'Email already verified' : 'Email verified');
        }

        return redirect()->away($verification->frontendResultUrl($status));
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        if ($member->email_verified_at !== null) {
            return $this->success(['email_verified' => true], 'Email already verified');
        }

        try {
            app(MemberEmailVerification::class)->send($member);
        } catch (\Throwable) {
            return $this->error('Could not send verification email', 503);
        }

        return $this->success(null, 'Verification email sent');
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

        return $twoFactor->verifyBackupCode($code);
    }
}
