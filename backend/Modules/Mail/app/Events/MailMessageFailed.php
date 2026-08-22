<?php

declare(strict_types=1);

namespace Modules\Mail\Events;

class MailMessageFailed
{
    public function __construct(
        public string $to,
        public string $subject,
        public string $error,
        public ?string $userId = null,
        public ?string $accountId = null,
    ) {}
}
