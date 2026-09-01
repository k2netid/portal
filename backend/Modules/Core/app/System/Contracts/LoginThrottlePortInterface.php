<?php

declare(strict_types=1);

namespace Modules\Core\System\Contracts;

interface LoginThrottlePortInterface
{
    /**
     * @return array{blocked: bool, retry_after: int, message: string}|null null when allowed
     */
    public function blockedState(string $realm, string $email, string $ipAddress): ?array;

    /**
     * @return array{blocked: bool, retry_after: int}
     */
    public function recordFailure(string $realm, string $email, string $ipAddress): array;

    public function recordSuccess(string $realm, string $email, string $ipAddress): void;
}
