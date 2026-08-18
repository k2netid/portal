<?php

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    /**
     * @var array<string, callable>
     */
    protected static array $clearers = [];

    /**
     * Register a cache clearer for a module.
     */
    public static function registerClearer(string $name, callable $callback): void
    {
        self::$clearers[$name] = $callback;
    }

    /**
     * Clear all system caches
     */
    public function clearAll(): void
    {
        Cache::flush();

        foreach (self::$clearers as $clearer) {
            $clearer();
        }
    }

    /**
     * Clear tag-related caches
     */
    public function clearTagCaches(): void
    {
        Cache::forget('tags_all');
        Cache::forget('tags_statistics');
    }

    /**
     * Clear media caches
     */
    public function clearMediaCaches(): void
    {
        Cache::forget('media_list');
    }

    /**
     * Clear user caches
     *
     * @param  int|string|null  $userId
     */
    public function clearUserCaches($userId = null): void
    {
        if ($userId) {
            Cache::forget("user_{$userId}");
            Cache::forget("user_activity_{$userId}");
        }
    }

    /**
     * Warm up important caches
     *
     * @deprecated Use CacheWarmingService instead
     */
    public function warmUp(): void
    {
        // Handled by CacheWarmingService
    }

    /**
     * Get cache key with consistent prefix
     */
    public function getKey(string $prefix, string $key): string
    {
        return $prefix.':'.$key;
    }

    /**
     * Invalidate cache by pattern (Redis only)
     */
    public function invalidateByPattern(string $pattern): int
    {
        if (! $this->isRedisAvailable()) {
            return 0;
        }

        try {
            $redis = Redis::connection();
            /** @var string $prefix */
            $prefix = config('cache.prefix', '');
            $keys = $redis->keys($prefix.':'.$pattern);

            if (empty($keys)) {
                return 0;
            }

            return $redis->del($keys);
        } catch (\Exception $e) {
            \Log::warning('Failed to invalidate cache by pattern: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * Check if Redis is available and configured
     */
    public function isRedisAvailable(): bool
    {
        $cacheDriver = config('cache.default');

        // Not using Redis
        if (! in_array($cacheDriver, ['redis', 'redis_failover', 'failover'])) {
            return false;
        }

        try {
            $redis = Redis::connection();
            $redis->ping();

            return true;
        } catch (\Exception $e) {
            \Log::debug('Redis not available: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get the preferred cache store based on availability
     * Returns 'redis' if available, otherwise 'file'
     */
    public function getPreferredStore(): string
    {
        return $this->isRedisAvailable() ? 'redis' : 'file';
    }

    /**
     * Get current cache driver info
     *
     * @return array{configured_driver: string|null, redis_available: bool, effective_driver: string, recommendation: string}
     */
    public function getCacheDriverInfo(): array
    {
        /** @var string|null $driver */
        $driver = config('cache.default');
        $redisAvailable = $this->isRedisAvailable();

        return [
            'configured_driver' => $driver,
            'redis_available' => $redisAvailable,
            'effective_driver' => $redisAvailable ? 'redis' : 'file',
            'recommendation' => $redisAvailable
                ? 'Redis is active - optimal performance'
                : 'Using file cache - consider enabling Redis for better performance',
        ];
    }

    /**
     * Smart cache remember - uses Redis if available, falls back to file
     *
     * @template TCacheValue
     *
     * @param  \Closure(): TCacheValue  $callback
     * @return TCacheValue
     */
    public function smartRemember(string $key, int $ttlSeconds, \Closure $callback): mixed
    {
        $store = $this->getPreferredStore();

        /** @var TCacheValue */
        return Cache::store($store)->remember($key, $ttlSeconds, $callback);
    }
}
