<?php

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Media\Models\File as Media;
use Modules\Core\System\Models\Language;

/**
 * Cache Warming Service
 * Pre-caches frequently accessed data to improve performance
 */
class CacheWarmingService
{
    /**
     * @var array<string, callable>
     */
    protected static array $warmers = [];

    /**
     * Register a cache warmer for a module.
     */
    public static function registerWarmer(string $name, callable $callback): void
    {
        self::$warmers[$name] = $callback;
    }

    /**
     * Cache key prefixes
     */
    const PREFIX_TAG = 'tag:';

    const PREFIX_TAG_LIST = 'tags:list';

    const PREFIX_MEDIA = 'media:';

    const PREFIX_LANGUAGE = 'languages:active';

    const PREFIX_STATISTICS = 'statistics:';

    /**
     * Default TTL in seconds
     */
    const TTL_SHORT = 300;      // 5 minutes

    const TTL_MEDIUM = 1800;    // 30 minutes

    const TTL_LONG = 3600;      // 1 hour

    const TTL_VERY_LONG = 86400; // 24 hours

    /**
     * Warm all important caches
     *
     * @return array<string, int>
     */
    public function warmAll(): array
    {
        $results = [
            'tags' => $this->warmTags(),
            'media' => $this->warmMedia(),
            'languages' => $this->warmLanguages(),
            'statistics' => $this->warmStatistics(),
        ];

        // Warm module-specific caches
        foreach (self::$warmers as $name => $warmer) {
            try {
                $results[$name] = $warmer();
            } catch (\Exception $e) {
                Log::channel('jobs')->error("Failed to warm cache for {$name}: ".$e->getMessage());
                $results[$name] = 0;
            }
        }

        return $results;
    }

    /**
     * Warm tags cache
     */
    public function warmTags(): int
    {
        $count = 0;

        try {
            $tags = Tag::orderBy('name')->get();
            Cache::put(self::PREFIX_TAG_LIST, $tags, self::TTL_VERY_LONG);
            $count++;

            Log::channel('jobs')->info("Cache warmed: {$count} tag items");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm tags cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Warm media cache
     */
    public function warmMedia(int $limit = 30): int
    {
        $count = 0;

        try {
            $recentMedia = Media::orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            foreach ($recentMedia as $media) {
                $key = self::PREFIX_MEDIA.$media->id;
                Cache::remember($key, self::TTL_MEDIUM, fn () => $media->load(['folder', 'usages']));
                $count++;
            }

            Log::channel('jobs')->info("Cache warmed: {$count} media items");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm media cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Warm languages cache
     */
    public function warmLanguages(): int
    {
        $count = 0;

        try {
            $languages = Language::where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            Cache::put(self::PREFIX_LANGUAGE, $languages, self::TTL_VERY_LONG);
            $count++;

            Log::channel('jobs')->info("Cache warmed: {$count} language items");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm languages cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Warm statistics cache
     */
    public function warmStatistics(): int
    {
        $count = 0;

        try {
            $stats = [
                'total_tags' => Tag::count(),
                'total_media' => Media::count(),
                'total_users' => DB::table('srv_auth_users')->count(),
            ];

            Cache::put(self::PREFIX_STATISTICS.'overview', $stats, self::TTL_SHORT);
            $count++;

            Log::channel('jobs')->info("Cache warmed: {$count} statistics items");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm statistics cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Warm cache for specific content type
     */
    public function warmByType(string $type, int $limit = 50): int
    {
        return match ($type) {
            'tags' => $this->warmTags(),
            'media' => $this->warmMedia($limit),
            'languages' => $this->warmLanguages(),
            'statistics' => $this->warmStatistics(),
            default => isset(self::$warmers[$type]) ? (self::$warmers[$type])() : 0,
        };
    }

    /**
     * Get cache warming statistics
     *
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        return [
            'tags_cached' => $this->countCacheKeys(self::PREFIX_TAG),
            'media_cached' => $this->countCacheKeys(self::PREFIX_MEDIA),
        ];
    }

    /**
     * Count cache keys by prefix (Redis only)
     */
    private function countCacheKeys(string $prefix): int
    {
        try {
            if (config('cache.default') === 'redis') {
                $redis = Redis::connection();
                /** @var string $cachePrefix */
                $cachePrefix = config('cache.prefix', '');
                $pattern = $cachePrefix.':'.$prefix.'*';
                $keys = $redis->keys($pattern);

                return count($keys);
            }
        } catch (\Exception $e) {
            Log::channel('jobs')->warning('Failed to count cache keys: '.$e->getMessage());
        }

        return 0;
    }
}
