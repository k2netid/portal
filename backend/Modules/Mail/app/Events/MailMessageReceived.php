<?php

declare(strict_types=1);

namespace Modules\Mail\Events;

use Modules\Mail\Models\MailMessage;

class MailMessageReceived
{
    public function __construct(
        public MailMessage $message,
    ) {}
}
