<?php

namespace Modules\Core\System\Traits;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\System\Jobs\ProcessOutboundWebhook;
use Modules\Core\System\Models\Webhook;

trait DispatchesWebhooks
{
    /**
     * Boot the trait to listen for Eloquent events.
     */
    public static function bootDispatchesWebhooks(): void
    {
        static::created(function (Model $model) {
            static::dispatchWebhookEvent($model, 'created');
        });

        static::updated(function (Model $model) {
            static::dispatchWebhookEvent($model, 'updated');
            if ($model->wasChanged('password')) {
                static::dispatchWebhookEvent($model, 'password_changed');
            }
        });

        static::deleted(function (Model $model) {
            static::dispatchWebhookEvent($model, 'deleted');
        });
    }

    /**
     * Dispatch webhook event.
     */
    protected static function dispatchWebhookEvent(Model $model, string $action): void
    {
        $entity = strtolower(class_basename($model));
        $eventName = "{$entity}.{$action}";

        // We find webhooks that subscribe to this event or '*'
        $webhooks = Webhook::where('is_active', true)->get()->filter(function ($webhook) use ($eventName, $entity) {
            $events = $webhook->events ?? [];

            return in_array($eventName, $events) || in_array('*', $events) || in_array("{$entity}.*", $events);
        });

        foreach ($webhooks as $webhook) {
            ProcessOutboundWebhook::dispatch($webhook, $eventName, [
                'event' => $eventName,
                'entity' => $entity,
                'data' => $model->toArray(),
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }
}
