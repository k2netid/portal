<?php

declare(strict_types=1);

namespace Modules\Core\System\Exceptions;

use Exception;

class StorageQuotaExceededException extends Exception
{
    public function __construct(
        public readonly int $limitBytes,
        public readonly int $usedBytes,
        public readonly int $requestedBytes,
    ) {
        $limitMb = (int) ceil($limitBytes / 1024 / 1024);
        $usedMb = round($usedBytes / 1024 / 1024, 1);
        parent::__construct("Storage quota exceeded ({$usedMb} MB used of {$limitMb} MB limit).");
    }
}
