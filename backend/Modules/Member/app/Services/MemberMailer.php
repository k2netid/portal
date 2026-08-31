<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\Mail;
use Modules\Core\System\Contracts\OutboundMailPortInterface;
use Modules\Core\System\Models\Extension;

/**
 * Shared outbound mail for reader account messages (verify, reset, email change).
 */
class MemberMailer
{
    public function send(string $to, string $subject, string $html): void
    {
        if (Extension::isProductActive('mail') && app()->bound(OutboundMailPortInterface::class)) {
            try {
                app(OutboundMailPortInterface::class)->send(
                    $to,
                    $subject,
                    $html,
                    queue: false,
                );

                return;
            } catch (\Throwable) {
                // Fall through to Laravel mailer.
            }
        }

        Mail::html($html, static function ($message) use ($to, $subject): void {
            $message->to($to)->subject($subject);
        });
    }

    public function frontendUrl(string $path, array $query = []): string
    {
        $base = config('app.frontend_url');
        $root = is_string($base) && $base !== '' ? $base : (string) config('app.url');
        $root = rtrim($root, '/');
        if (str_ends_with($root, '/site')) {
            $root = rtrim(substr($root, 0, -strlen('/site')), '/');
        }

        $url = $root.'/'.ltrim($path, '/');
        if ($query !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?').http_build_query($query);
        }

        return $url;
    }
}
