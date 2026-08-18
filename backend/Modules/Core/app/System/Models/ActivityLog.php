<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string|null $description
 * @property array<string, mixed>|null $changes
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Model|null $model
 */
class ActivityLog extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'system_activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    public static function log(string $action, ?Model $model = null, array $changes = [], ?User $user = null, ?string $description = null): self
    {
        $user ??= auth()->user();

        return self::create([
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $model instanceof Model ? $model::class : null,
            'model_id' => $model?->getKey(),
            'description' => $description ?? self::generateDescription($action, $model),
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected static function generateDescription(string $action, ?Model $model = null): string
    {
        $modelName = $model instanceof Model ? class_basename($model) : 'item';

        return match ($action) {
            'created' => "Created {$modelName}",
            'updated' => "Updated {$modelName}",
            'deleted' => "Deleted {$modelName}",
            default => ucfirst($action)." {$modelName}",
        };
    }
}
