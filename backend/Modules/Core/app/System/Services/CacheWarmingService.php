<?php

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\Language;
use Modules\Core\System\Models\Setting;

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
    const PREFIX_LANGUAGE = 'languages:active';

    const PREFIX_SETTINGS = 'settings:public';

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
            'languages' => $this->warmLanguages(),
            'settings' => $this->warmPublicSettings(),
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
            $count = $languages->count();

            Log::channel('jobs')->info("Cache warmed: {$count} active languages");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm languages cache: '.$e->getMessage());
        }

        return $count;
    }

    /**
     * Warm public settings cache
     */
    public function warmPublicSettings(): int
    {
        $count = 0;

        try {
            $settings = Setting::where('is_public', true)->get();
            Cache::put(self::PREFIX_SETTINGS, $settings, self::TTL_LONG);
            $count = $settings->count();

            Log::channel('jobs')->info("Cache warmed: {$count} public settings");
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm public settings cache: '.$e->getMessage());
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
            $systemService = app(SystemService::class);
            $systemService->getSystemInfo();
            $count++;

            Log::channel('jobs')->info('Cache warmed: system statistics');
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Failed to warm statistics cache: '.$e->getMessage());
        }

        return $count;
    }
}
