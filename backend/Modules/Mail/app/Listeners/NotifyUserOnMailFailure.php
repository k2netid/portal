<?php

declare(strict_types=1);

namespace Modules\Mail\Listeners;

use Modules\Core\System\Models\Notification;
use Modules\Mail\Events\MailMessageFailed;

class NotifyUserOnMailFailure
{
    public function handle(MailMessageFailed $event): void
    {
        if (! is_string($event->userId) || $event->userId === '') {
            return;
        }

        Notification::createForUser(
            $event->userId,
            'error',
            'Mail send failed',
            'Could not send “'.$event->subject.'” to '.$event->to.': '.$event->error,
            'mail',
            'Open JA-Mail',
            [
                'source' => 'mail',
                'to' => $event->to,
                'account_id' => $event->accountId,
            ],
        );
    }
}
