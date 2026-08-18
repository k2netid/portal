<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Models\RedisSetting;
use Modules\Core\System\Models\Setting;

class RedisConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * Load Redis settings from database and merge with config.
     */
    public function boot(): void
    {
        // Only load if sys_redis_settings table exists and we're not in console during migration
        if ($this->app->runningInConsole() && $this->isMigrating()) {
            return;
        }

        try {
            if (! Schema::hasTable('sys_redis_settings')) {
                return;
            }

            $this->loadRedisSettingsFromDatabase();
        } catch (\Exception $e) {
            // Silently fail if database is not available
            Log::debug('RedisConfigServiceProvider: Could not load settings - '.$e->getMessage());
        }
    }

    /**
     * Check if we're running migrations
     */
    protected function isMigrating(): bool
    {
        /** @var array<int, string> $argv */
        $argv = $_SERVER['argv'] ?? [];

        return in_array('migrate', $argv, true)
            || in_array('migrate:fresh', $argv, true)
            || in_array('migrate:refresh', $argv, true);
    }

    /**
     * Load Redis settings from database and apply to config
     */
    protected function loadRedisSettingsFromDatabase(): void
    {
        // Use model collection to properly apply value accessor (decryption)
        $redisSettings = RedisSetting::all();

        if ($redisSettings->isEmpty()) {
            return;
        }

        // Build settings array using model accessor (handles decryption)
        $settings = $redisSettings->mapWithKeys(function ($setting): array {
            return [(string) $setting->key => $setting->value]; // This uses getValueAttribute accessor
        });

        // Map RedisSetting keys to config values
        $configMap = [
            'redis_host' => 'database.redis.default.host',
            'redis_port' => 'database.redis.default.port',
            'redis_username' => 'database.redis.default.username',
            'redis_password' => 'database.redis.default.password',
            'redis_database' => 'database.redis.default.database',
            'redis_cache_database' => 'database.redis.cache.database',
            'redis_prefix' => 'database.redis.options.prefix',
        ];

        foreach ($settings as $key => $value) {
            $stringKey = (string) $key;
            // Skip empty or literal 'null' values - use .env defaults
            if (in_array($value, [null, '', 'null'], true)) {
                continue;
            }

            // Apply config if mapping exists
            if (isset($configMap[$stringKey])) {
                config([$configMap[$stringKey] => $value]);

                // Also update cache connection for consistency
                if ($stringKey === 'redis_host') {
                    config(['database.redis.cache.host' => $value]);
                }
                if ($stringKey === 'redis_port') {
                    config(['database.redis.cache.port' => $value]);
                }
                if ($stringKey === 'redis_password') {
                    config(['database.redis.cache.password' => $value]);
                }
                if ($stringKey === 'redis_username') {
                    config(['database.redis.cache.username' => $value]);
                }
            }
        }

        // Handle Queue Configuration
        if (isset($settings['queue_enabled'])) {
            $isEnabled = filter_var($settings['queue_enabled'], FILTER_VALIDATE_BOOLEAN);
            config(['queue.default' => $isEnabled ? 'redis' : 'sync']);
        }

        // Handle Session Configuration
        if (isset($settings['session_enabled'])) {
            $isEnabled = filter_var($settings['session_enabled'], FILTER_VALIDATE_BOOLEAN);
            config(['session.driver' => $isEnabled ? 'redis' : 'file']);
        }

        // Sync global cache driver with Performance settings (settings table).
        // PerformanceTab writes `cache_driver` there, but runtime config reads cache.default.
        try {
            if (Schema::hasTable('sys_settings')) {
                $selectedDriver = Setting::get('cache_driver');
                if (is_string($selectedDriver) && $selectedDriver !== '') {
                    // `redis_failover` is UI alias; safest runtime fallback is Redis.
                    $resolved = $selectedDriver === 'redis_failover' ? 'redis' : $selectedDriver;
                    config(['cache.default' => $resolved]);
                }
            }
        } catch (\Exception $e) {
            Log::debug('RedisConfigServiceProvider: Could not apply performance cache_driver - '.$e->getMessage());
        }
    }
}
