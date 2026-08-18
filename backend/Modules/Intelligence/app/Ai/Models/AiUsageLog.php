<?php

declare(strict_types=1);

namespace Modules\Intelligence\Ai\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string $feature
 * @property string|null $provider
 * @property int $tokens_in
 * @property int $tokens_out
 */
class AiUsageLog extends Model
{
    use HasUuids;

    protected $table = 'ai_usage_logs';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'feature',
        'provider',
        'tokens_in',
        'tokens_out',
        'duration_ms',
    ];

    protected $casts = [
        'tokens_in' => 'integer',
        'tokens_out' => 'integer',
        'duration_ms' => 'integer',
    ];
}
