<?php

declare(strict_types=1);

namespace Modules\Intelligence\Ai\Services\Exceptions;

use RuntimeException;

class AiSubscriptionQuotaExceededException extends RuntimeException
{
    public function __construct(
        public readonly int $limit,
        public readonly int $used,
        public readonly int $requested,
    ) {
        parent::__construct(
            "AI monthly token quota exceeded (limit: {$limit}, used: {$used}, requested: {$requested}).",
        );
    }
}
