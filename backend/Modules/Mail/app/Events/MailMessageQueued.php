<?php

declare(strict_types=1);

namespace Modules\Mail\Events;

class MailMessageQueued
{
    /**
     * @param  array<int, string>  $cc
     * @param  array<int, string>  $bcc
     */
    public function __construct(
        public string $to,
        public string $subject,
        public ?string $accountId,
        public ?string $userId,
        public array $cc = [],
        public array $bcc = [],
    ) {}
}
