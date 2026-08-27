<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Modules\Core\System\Contracts\OutboundMailPortInterface;
use Modules\Member\Models\Member;

class MemberEmailVerification
{
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

        if (app()->bound(OutboundMailPortInterface::class)) {
            try {
                app(OutboundMailPortInterface::class)->send(
                    $member->email,
                    $subject,
                    $html,
                    queue: false,
                );

                return;
            } catch (\Throwable) {
                // JA-Mail may be bound without a usable account; Laravel mailer is the fallback.
            }
        }

        Mail::html($html, static function ($message) use ($member, $subject): void {
            $message->to($member->email)->subject($subject);
        });
    }

    public function frontendResultUrl(string $status): string
    {
        $base = config('app.frontend_url');
        $root = is_string($base) && $base !== '' ? $base : (string) config('app.url');
        $root = rtrim($root, '/');
        if (! str_ends_with($root, '/site')) {
            $root .= '/site';
        }

        return $root.'/member/verified?status='.rawurlencode($status);
    }
}
