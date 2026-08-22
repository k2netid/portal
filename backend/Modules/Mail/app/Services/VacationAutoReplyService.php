<?php

declare(strict_types=1);

namespace Modules\Mail\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Contracts\OutboundMailPortInterface;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Mail\Events\VacationAutoReplySent;
use Modules\Mail\Models\MailMessage;

/**
 * Out-of-office auto-reply for newly ingested inbox messages.
 * Call via MailboxIngestService when messages enter the local inbox.
 */
class VacationAutoReplyService
{
    public function __construct(
        protected OutboundMailPortInterface $outboundMail,
    ) {}

    public function maybeReply(MailMessage $inbound): bool
    {
        if ($inbound->folder !== 'inbox') {
            return false;
        }

        if (! is_string($inbound->user_id) || $inbound->user_id === '') {
            return false;
        }

        $user = User::query()->find($inbound->user_id);
        if (! $user instanceof User) {
            return false;
        }

        if (! $this->isEnabled($user)) {
            return false;
        }

        $sender = strtolower(trim((string) $inbound->sender_email));
        if ($sender === '' || ! filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $userEmail = strtolower(trim((string) $user->email));
        if ($userEmail !== '' && $sender === $userEmail) {
            return false;
        }

        $subject = (string) $inbound->subject;
        if ($this->looksLikeAutoReply($subject)) {
            return false;
        }

        $cacheKey = 'mail:vacation:'.$user->id.':'.sha1($sender);
        if (! Cache::add($cacheKey, true, now()->addDay())) {
            return false;
        }

        $replySubject = $this->settingString($user, 'vacation_subject', 'Out of Office Auto-Reply');
        $replyBody = $this->settingString(
            $user,
            'vacation_body',
            'Thank you for your message. I am currently out of office.',
        );

        try {
            $this->outboundMail->send(
                $sender,
                $replySubject,
                nl2br(e($replyBody)),
                asUser: $user,
                queue: true,
            );
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            Log::warning('Vacation auto-reply failed', [
                'user_id' => $user->id,
                'to' => $sender,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        Event::dispatch(new VacationAutoReplySent($user->id, $sender, $replySubject));

        return true;
    }

    private function isEnabled(User $user): bool
    {
        $key = 'mail_client_vacation_enabled_user_'.$user->id;
        $value = Setting::get($key);
        if ($value === null) {
            $value = Setting::get('mail_client_vacation_enabled', false);
        }

        return is_bool($value) ? $value : (bool) $value;
    }

    private function settingString(User $user, string $suffix, string $default): string
    {
        $key = 'mail_client_'.$suffix.'_user_'.$user->id;
        $value = Setting::get($key);
        if (! is_string($value) || $value === '') {
            $global = Setting::get('mail_client_'.$suffix, $default);

            return is_string($global) && $global !== '' ? $global : $default;
        }

        return $value;
    }

    private function looksLikeAutoReply(string $subject): bool
    {
        return (bool) preg_match('/\b(auto[- ]?reply|out of office|ooo|vacation)\b/i', $subject);
    }
}
