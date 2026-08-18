<?php

namespace Modules\Intelligence\Analytics\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $query
 * @property int $duration
 * @property string|null $route
 */
class SlowQuery extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'srv_analytics_slow_queries';

    protected $fillable = [
        'sql',
        'time',
        'connection',
        'url',
    ];

    protected $casts = [
        'time' => 'float',
    ];

    protected $appends = [
        /** @phpstan-ignore-next-line */
        'query',
        'duration',
        'route',
    ];

    public function getQueryAttribute(): ?string
    {
        return isset($this->attributes['sql']) && is_scalar($this->attributes['sql']) ? (string) $this->attributes['sql'] : null;
    }

    public function getDurationAttribute(): int
    {
        $time = $this->attributes['time'] ?? 0;

        return is_numeric($time) ? (int) round((float) $time) : 0;
    }

    public function getRouteAttribute(): ?string
    {
        return isset($this->attributes['url']) && is_scalar($this->attributes['url']) ? (string) $this->attributes['url'] : null;
    }

    /**
     * @param  Builder<$this>  $query
     * @param  int|float|null  $threshold
     * @return Builder<$this>
     */
    public function scopeSlow($query, $threshold = 1000)
    {
        return $query->where('time', '>=', $threshold ?? 1000);
    }

    /**
     * @param  Builder<$this>  $query
     * @param  string  $route
     * @return Builder<$this>
     */
    public function scopeByRoute($query, $route)
    {
        return $query->where('url', $route);
    }
}
