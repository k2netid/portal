<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\URL;
use Modules\Member\Models\Member;

class MemberEmailChange
{
    public function __construct(
        private MemberMailer $mailer,
    ) {}

    public function request(Member $member, string $newEmail): void
    {
        $member->forceFill(['pending_email' => $newEmail])->save();

        $url = URL::temporarySignedRoute(
            'member.confirm-email-change',
            now()->addHours(24),
            [
                'id' => $member->id,
                'hash' => $this->hashFor($newEmail),
            ],
        );

        $subject = 'Confirm your new email';
        $html = '<p>Confirm changing your reader account email to <strong>'.e($newEmail).'</strong>.</p>'
            .'<p><a href="'.e($url).'">Confirm email change</a></p>'
            .'<p>This link expires in 24 hours.</p>';

        $this->mailer->send($newEmail, $subject, $html);
    }

    public function hashFor(string $email): string
    {
        return sha1($email);
    }

    public function isValidHash(string $email, string $hash): bool
    {
        return hash_equals($this->hashFor($email), $hash);
    }

    /**
     * @return 'ok'|'invalid'|'mismatch'
     */
    public function confirm(Member $member, string $hash): string
    {
        $pending = $member->pending_email;
        if (! is_string($pending) || $pending === '') {
            return 'invalid';
        }

        if (! $this->isValidHash($pending, $hash)) {
            return 'mismatch';
        }

        if (Member::query()->where('email', $pending)->whereKeyNot($member->id)->exists()) {
            return 'invalid';
        }

        $member->forceFill([
            'email' => $pending,
            'pending_email' => null,
            'email_verified_at' => now(),
        ])->save();

        // Revoke all reader sessions — email change is a security boundary.
        $member->tokens()->delete();
        \Laravel\Sanctum\PersonalAccessToken::query()
            ->where('tokenable_type', $member->getMorphClass())
            ->where('tokenable_id', $member->getKey())
            ->delete();

        return 'ok';
    }

    public function frontendResultUrl(string $status): string
    {
        return app(MemberMailer::class)->frontendUrl('/member/email-changed', ['status' => $status]);
    }
}
