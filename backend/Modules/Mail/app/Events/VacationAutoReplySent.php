<?php

declare(strict_types=1);

namespace Modules\Mail\Events;

class VacationAutoReplySent
{
    public function __construct(
        public string $userId,
        public string $to,
        public string $subject,
    ) {}
}
