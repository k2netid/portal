<?php

declare(strict_types=1);

namespace Modules\Mail\Listeners;

use Modules\Core\System\Models\Notification;
use Modules\Mail\Events\VacationAutoReplySent;

class NotifyUserOnVacationAutoReply
{
    public function handle(VacationAutoReplySent $event): void
    {
        Notification::createForUser(
            $event->userId,
            'info',
            'Vacation auto-reply sent',
            'Out-of-office reply “'.$event->subject.'” was queued to '.$event->to.'.',
            'mail',
            'Open JA-Mail',
            [
                'source' => 'mail',
                'to' => $event->to,
            ],
        );
    }
}
