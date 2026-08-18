<?php

declare(strict_types=1);

namespace Modules\Core\System\Contracts;

interface OutboundWebhookPortInterface
{
    /** @param array<string, mixed> $payload */
    public function dispatch(string $event, array $payload): void;
}
