<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Modules\Core\System\Models\Setting;
use Modules\Member\Contracts\MemberSecurityAuditPortInterface;
use Modules\Member\Models\Member;

class MemberGoogleAuthService
{
    public const STATE_TTL_SECONDS = 600;

    public const PENDING_2FA_TTL_SECONDS = 300;

    public function isAvailable(): bool
    {
        if (! (bool) Setting::get('enable_member_google_login', true)) {
            return false;
        }

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        return is_string($clientId) && $clientId !== ''
            && is_string($clientSecret) && $clientSecret !== '';
    }

    public function storeOAuthState(string $redirectPath): string
    {
        $key = Str::random(40);
        Cache::put($this->stateCacheKey($key), [
            'redirect' => $this->sanitizeRedirectPath($redirectPath),
        ], self::STATE_TTL_SECONDS);

        return $key;
    }

    /**
     * @return array{redirect: string}|null
     */
    public function pullOAuthState(?string $key): ?array
    {
        if ($key === null || $key === '') {
            return null;
        }

        $payload = Cache::pull($this->stateCacheKey($key));
        if (! is_array($payload)) {
            return null;
        }

        $redirect = isset($payload['redirect']) && is_string($payload['redirect'])
            ? $payload['redirect']
            : '/member';

        return ['redirect' => $this->sanitizeRedirectPath($redirect)];
    }

    /**
     * Find or create a member from a Google profile.
     *
     * @return array{member: Member, created: bool}
     */
    public function resolveMember(SocialiteUser $googleUser): array
    {
        $googleId = (string) $googleUser->getId();
        $email = strtolower(trim((string) $googleUser->getEmail()));
        $name = trim((string) ($googleUser->getName() ?: $googleUser->getNickname() ?: 'Member'));

        if ($googleId === '' || $email === '') {
            throw new \InvalidArgumentException('Google account did not return an email address.');
        }

        $byGoogle = Member::query()->where('google_id', $googleId)->first();
        if ($byGoogle instanceof Member) {
            return ['member' => $byGoogle, 'created' => false];
        }

        $byEmail = Member::query()->where('email', $email)->first();
        if ($byEmail instanceof Member) {
            $byEmail->forceFill([
                'google_id' => $googleId,
                'email_verified_at' => $byEmail->email_verified_at ?? now(),
                'avatar' => $byEmail->avatar ?: $this->avatarFromGoogle($googleUser),
            ])->save();

            return ['member' => $byEmail->fresh() ?? $byEmail, 'created' => false];
        }

        if (! (bool) Setting::get('enable_member_registration', true)) {
            throw new \RuntimeException('Member registration is currently disabled.');
        }

        $member = Member::query()->create([
            'name' => $name !== '' ? $name : 'Member',
            'email' => $email,
            'google_id' => $googleId,
            'password' => Hash::make(Str::random(64)),
            'status' => 'active',
            'email_verified_at' => now(),
            'avatar' => $this->avatarFromGoogle($googleUser),
        ]);

        return ['member' => $member, 'created' => true];
    }

    public function issueToken(Member $member): string
    {
        $member->forceFill(['last_login_at' => now()])->save();

        return $member->createToken('member')->plainTextToken;
    }

    public function storePendingTwoFactor(Member $member): string
    {
        $pending = Str::random(64);
        Cache::put($this->pending2faCacheKey($pending), $member->id, self::PENDING_2FA_TTL_SECONDS);

        return $pending;
    }

    public function pullPendingTwoFactorMember(string $pending): ?Member
    {
        $memberId = Cache::get($this->pending2faCacheKey($pending));
        if (! is_string($memberId) || $memberId === '') {
            return null;
        }

        $member = Member::query()->find($memberId);

        return $member instanceof Member ? $member : null;
    }

    public function forgetPendingTwoFactor(string $pending): void
    {
        Cache::forget($this->pending2faCacheKey($pending));
    }

    public function frontendCallbackUrl(array $query): string
    {
        return app(MemberMailer::class)->frontendUrl('/member/oauth/callback', $query);
    }

    public function frontendLoginUrl(array $query): string
    {
        return app(MemberMailer::class)->frontendUrl('/member/login', $query);
    }

    public function audit(string $event, ?Member $member, string $description, ?string $ip = null): void
    {
        app(MemberSecurityAuditPortInterface::class)->record($event, $member, $description, [], $ip);
    }

    private function sanitizeRedirectPath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || ! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return '/member';
        }

        return $path;
    }

    private function avatarFromGoogle(SocialiteUser $googleUser): ?string
    {
        $avatar = $googleUser->getAvatar();

        return is_string($avatar) && $avatar !== '' ? $avatar : null;
    }

    private function stateCacheKey(string $key): string
    {
        return 'member_google_oauth_state:'.$key;
    }

    private function pending2faCacheKey(string $pending): string
    {
        return 'member_google_oauth_2fa:'.$pending;
    }
}
