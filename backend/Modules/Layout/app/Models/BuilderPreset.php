<?php

namespace Modules\Layout\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\System\Models\User;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string $type
 * @property string $name
 * @property array<string, mixed> $settings
 * @property bool $is_system
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BuilderPreset extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'lay_builder_presets';

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'settings',
        'is_system',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_system' => 'boolean',
    ];

    /**
     * Get the user that owns the preset.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for system presets.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for user presets.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @param  int|string  $userId
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)->where('is_system', false);
    }

    /**
     * Scope by module type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
