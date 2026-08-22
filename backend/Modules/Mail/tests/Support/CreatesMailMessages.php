<?php

declare(strict_types=1);

namespace Modules\Mail\Tests\Support;

use Modules\Core\System\Models\User;
use Modules\Mail\Models\MailMessage;

trait CreatesMailMessages
{
    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createMailMessage(User $user, array $overrides = []): MailMessage
    {
        return MailMessage::create(array_merge([
            'user_id' => $user->id,
            'folder' => 'inbox',
            'sender_name' => 'Support Team',
            'sender_email' => 'support@example.com',
            'recipients' => [$user->email],
            'subject' => 'Test Subject',
            'snippet' => 'Snippet',
            'body' => '<p>Body</p>',
            'is_read' => false,
            'is_starred' => false,
            'labels' => [],
        ], $overrides));
    }
}
