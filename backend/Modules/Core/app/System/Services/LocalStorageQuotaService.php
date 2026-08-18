<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Modules\Core\System\Contracts\StorageQuotaServiceInterface;
use Modules\Core\System\Exceptions\StorageQuotaExceededException;

class LocalStorageQuotaService implements StorageQuotaServiceInterface
{
    /**
     * Storage limit in bytes (defaults to 10GB for standalone CMS).
     */
    public function getLimitBytes(?string $workspaceId = null): int
    {
        return 10 * 1024 * 1024 * 1024; // 10 GB
    }

    /**
     * Bytes already used.
     */
    public function getUsedBytes(?string $workspaceId = null): int
    {
        return 0;
    }

    /**
     * @throws StorageQuotaExceededException
     */
    public function assertCanStore(int $additionalBytes, ?string $workspaceId = null): void
    {
        // Standalone CMS has no strict quota blocks
    }
}
