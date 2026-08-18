<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Core\System\Contracts\OutboundWebhookPortInterface;

/**
 * @property string $id
 * @property string $name
 * @property string $url
 * @property array<int, string>|null $events
 * @property string|null $secret
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, WebhookDelivery> $deliveries
 */
class Webhook extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'infra_webhooks';

    protected $fillable = [
        'name',
        'url',
        'events',
        'secret',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<WebhookDelivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'webhook_id');
    }

    /**
     * Attempt to deliver a test payload (stub: no HTTP call).
     *
     * @param  array<string, mixed>  $data
     */
    public function trigger(array $data = []): bool
    {
        return false;
    }

    /**
     * Stub for triggerForEvent to avoid breaking dependencies.
     *
     * @param  array<string, mixed>  $data
     */
    public static function triggerForEvent(string $event, array $data): void
    {
        app(OutboundWebhookPortInterface::class)->dispatch($event, [
            'entity' => explode('.', $event, 2)[0] ?? 'system',
            'data' => $data,
        ]);
    }
}
