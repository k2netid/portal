<?php

declare(strict_types=1);

namespace Modules\CmsAi\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string|null $subscription_id
 * @property string $status
 * @property int $total_items
 * @property int $completed_items
 * @property int $failed_items
 * @property array<int, mixed>|null $items
 * @property array<int, mixed>|null $results
 * @property string|null $error_message
 * @property string|null $provider
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AiTaxonomyBatch extends Model
{
    use HasUuids;

    protected $table = 'ai_taxonomy_batches';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'status',
        'total_items',
        'completed_items',
        'failed_items',
        'items',
        'results',
        'error_message',
        'provider',
    ];

    protected $casts = [
        'items' => 'array',
        'results' => 'array',
        'total_items' => 'integer',
        'completed_items' => 'integer',
        'failed_items' => 'integer',
    ];
}
