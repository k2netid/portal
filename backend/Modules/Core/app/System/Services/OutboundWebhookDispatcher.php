<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Modules\Core\System\Contracts\OutboundWebhookPortInterface;
use Modules\Core\System\Jobs\ProcessOutboundWebhook;
use Modules\Core\System\Models\Webhook;

class OutboundWebhookDispatcher implements OutboundWebhookPortInterface
{
    /** @param array<string, mixed> $payload */
    public function dispatch(string $event, array $payload): void
    {
        $webhooks = Webhook::query()->where('is_active', true)->get()->filter(
            function (Webhook $webhook) use ($event): bool {
                $events = $webhook->events ?? [];
                if (! is_array($events)) {
                    return false;
                }
                $parts = explode('.', $event, 2);
                $entity = $parts[0] ?? '';

                return in_array($event, $events, true)
                    || in_array('*', $events, true)
                    || ($entity !== '' && in_array($entity.'.*', $events, true));
            }
        );

        $body = array_merge([
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
        ], $payload);

        foreach ($webhooks as $webhook) {
            ProcessOutboundWebhook::dispatch($webhook, $event, $body);
        }
    }
}
