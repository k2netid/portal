<?php

declare(strict_types=1);

namespace Modules\Mail\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\User;
use Modules\Mail\Events\MailMessageReceived;
use Modules\Mail\Models\MailMessage;

/**
 * Single entry point for placing messages into a user's inbox.
 * Downstream IMAP sync should call ingest() so vacation + hooks fire consistently.
 */
class MailboxIngestService
{
    public function __construct(
        protected VacationAutoReplyService $vacation,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function ingest(User $user, array $attributes = []): MailMessage
    {
        $message = MailMessage::create(array_merge([
            'user_id' => $user->id,
            'folder' => 'inbox',
            'sender_name' => 'Unknown',
            'sender_email' => 'unknown@example.com',
            'recipients' => [$user->email],
            'subject' => '(No Subject)',
            'snippet' => '',
            'body' => '',
            'is_read' => false,
            'is_starred' => false,
            'labels' => [],
            'attachments' => [],
            'received_at' => now(),
            'message_id' => '<'.Str::uuid()->toString().'@jejakawan.local>',
        ], $attributes, [
            'user_id' => $user->id,
            'folder' => 'inbox',
        ]));

        Event::dispatch(new MailMessageReceived($message));
        Hook::action('mail.message_received', $message);

        $this->vacation->maybeReply($message);

        return $message->fresh() ?? $message;
    }
}
