<?php

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Query Cache Service
 * Provides centralized caching for expensive database queries
 * with consistent TTL and cache key management.
 */
class QueryCacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    public const TTL_SHORT = 300;      // 5 minutes

    public const TTL_MEDIUM = 1800;    // 30 minutes

    public const TTL_LONG = 3600;      // 1 hour

    public const TTL_DAY = 86400;      // 24 hours

    /**
     * Cache a query result with automatic key generation
     *
     * @param  string  $key  Cache key
     * @param  \Closure(): mixed  $callback  Query callback
     * @param  int  $ttl  Time to live in seconds
     */
    public function remember(string $key, \Closure $callback, int $ttl = self::TTL_MEDIUM): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Cache analytics data (longer TTL)
     *
     * @param  string  $key  Cache key
     * @param  \Closure(): mixed  $callback  Query callback
     */
    public function rememberAnalytics(string $key, \Closure $callback): mixed
    {
        return $this->remember("analytics:{$key}", $callback, self::TTL_LONG);
    }

    /**
     * Cache dashboard data (medium TTL)
     *
     * @param  string  $key  Cache key
     * @param  \Closure(): mixed  $callback  Query callback
     */
    public function rememberDashboard(string $key, \Closure $callback): mixed
    {
        return $this->remember("dashboard:{$key}", $callback, self::TTL_MEDIUM);
    }

    /**
     * Cache statistics (long TTL)
     *
     * @param  string  $key  Cache key
     * @param  \Closure(): mixed  $callback  Query callback
     */
    public function rememberStats(string $key, \Closure $callback): mixed
    {
        return $this->remember("stats:{$key}", $callback, self::TTL_LONG);
    }

    /**
     * Invalidate cache by pattern
     *
     * @param  string  $pattern  Cache key pattern (e.g., 'analytics:*')
     */
    public function forget(string $pattern): void
    {
        if (str_contains($pattern, '*')) {
            // For Redis, use pattern matching
            if (config('cache.default') === 'redis') {
                $keys = Redis::keys($pattern);
                if (! empty($keys)) {
                    Redis::del($keys);
                }
            } else {
                // For file cache, just forget the exact key
                Cache::forget(str_replace('*', '', $pattern));
            }
        } else {
            Cache::forget($pattern);
        }
    }

    /**
     * Invalidate all analytics cache
     */
    public function forgetAnalytics(): void
    {
        $this->forget('analytics:*');
    }

    /**
     * Invalidate all dashboard cache
     */
    public function forgetDashboard(): void
    {
        $this->forget('dashboard:*');
    }

    /**
     * Invalidate all statistics cache
     */
    public function forgetStats(): void
    {
        $this->forget('stats:*');
    }

    /**
     * Generate cache key from parameters
     *
     * @param  string  $prefix  Key prefix
     * @param  array<mixed>  $params  Parameters to include in key
     */
    public function generateKey(string $prefix, array $params = []): string
    {
        if ($params === []) {
            return $prefix;
        }

        $hash = md5(serialize($params));

        return "{$prefix}:{$hash}";
    }
}
