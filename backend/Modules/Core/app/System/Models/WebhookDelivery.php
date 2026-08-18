<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $webhook_id
 * @property string $event
 * @property int $attempt
 * @property int|null $status_code
 * @property string $status
 * @property string|null $response_body
 * @property string|null $error_message
 * @property int|null $duration_ms
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Webhook $webhook
 */
class WebhookDelivery extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'infra_webhook_deliveries';

    protected $fillable = [
        'webhook_id',
        'event',
        'attempt',
        'status_code',
        'status',
        'response_body',
        'error_message',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'attempt' => 'integer',
            'status_code' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Webhook, $this>
     */
    /**
     * @return BelongsTo<Webhook, $this>
     */
    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class, 'webhook_id');
    }
}
