<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\URL;
use Modules\Member\Models\Member;

class MemberEmailVerification
{
    public function __construct(
        private MemberMailer $mailer,
    ) {}

    public function signedUrl(Member $member): string
    {
        return URL::temporarySignedRoute(
            'member.verify-email',
            now()->addHours(24),
            [
                'id' => $member->id,
                'hash' => sha1((string) $member->email),
            ],
        );
    }

    public function hashFor(Member $member): string
    {
        return sha1((string) $member->email);
    }

    public function isValidHash(Member $member, string $hash): bool
    {
        return hash_equals($this->hashFor($member), $hash);
    }

    public function send(Member $member): void
    {
        $url = $this->signedUrl($member);
        $subject = 'Verify your email';
        $html = '<p>Confirm your reader account email ('.e($member->email).').</p><p><a href="'.e($url).'">Verify email</a></p>';

        $this->mailer->send($member->email, $subject, $html);
    }

    public function frontendResultUrl(string $status): string
    {
        return $this->mailer->frontendUrl('/member/verified', ['status' => $status]);
    }
}
