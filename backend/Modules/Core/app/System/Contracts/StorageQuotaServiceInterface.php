<?php

declare(strict_types=1);

namespace Modules\Core\System\Contracts;

use Modules\Core\System\Exceptions\StorageQuotaExceededException;

interface StorageQuotaServiceInterface
{
    /**
     * Storage limit in bytes for the given organization (null = current context organization).
     */
    public function getLimitBytes(?string $workspaceId = null): int;

    /**
     * Bytes already used by media records for the organization.
     */
    public function getUsedBytes(?string $workspaceId = null): int;

    /**
     * @throws StorageQuotaExceededException
     */
    public function assertCanStore(int $additionalBytes, ?string $workspaceId = null): void;
}
