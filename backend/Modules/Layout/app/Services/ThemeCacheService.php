<?php

namespace Modules\Layout\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Layout\Models\Theme;

/**
 * Service for theme caching operations
 */
class ThemeCacheService
{
    /**
     * Cache key prefixes
     */
    const PREFIX_ACTIVE = 'theme.active.';

    /**
     * Serialized public JSON payload for ThemeController::getActive (per type).
     */
    const PREFIX_ACTIVE_API_PAYLOAD = 'theme.active.api.payload.';

    const PREFIX_SETTINGS = 'theme.settings.';

    const PREFIX_ASSETS = 'theme.assets.';

    const PREFIX_MANIFEST = 'theme.manifest.';

    const PREFIX_TEMPLATES = 'theme.templates.';

    const PREFIX_PARTIALS = 'theme.partials.';

    const PREFIX_LAYOUTS = 'theme.layouts.';

    const PREFIX_WIDGETS = 'theme.widgets.';

    const PREFIX_SHORTCODES = 'theme.shortcodes.';

    /**
     * Cache TTL (Time To Live) in seconds
     */
    const TTL_SHORT = 300;      // 5 minutes

    const TTL_MEDIUM = 1800;    // 30 minutes

    const TTL_LONG = 3600;      // 1 hour

    const TTL_VERY_LONG = 86400; // 24 hours

    /**
     * Get active theme with cache
     *
     * @param  (callable(): (?Theme))|null  $callback
     */
    public function getActiveTheme(string $type = 'frontend', ?callable $callback = null): ?Theme
    {
        $cacheKey = self::PREFIX_ACTIVE.$type;

        $theme = Cache::remember($cacheKey, self::TTL_LONG, function () use ($callback) {
            if ($callback) {
                return $callback();
            }

            return null;
        });

        return $theme instanceof Theme ? $theme : null;
    }

    /**
     * Check if cache tags are supported
     */
    protected function tagsSupported(): bool
    {
        $driver = config('cache.default');

        return is_string($driver) && in_array($driver, ['redis', 'memcached']);
    }

    /**
     * Cache theme settings
     *
     * @param  callable(): mixed  $callback
     * @return mixed
     */
    public function rememberSettings(Theme $theme, callable $callback)
    {
        $cacheKey = self::PREFIX_SETTINGS.$theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_LONG,
                fn () => $callback()
            );
        }

        return Cache::remember($cacheKey, self::TTL_LONG, fn () => $callback());
    }

    /**
     * Cache theme assets
     *
     * @param  callable(): array{css: array<int, string>, js: array<int, string>}  $callback
     * @return array{css: array<int, string>, js: array<int, string>}
     */
    public function rememberAssets(Theme $theme, callable $callback): array
    {
        $cacheKey = self::PREFIX_ASSETS.$theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_VERY_LONG,
                fn (): array => $callback()
            );
        }

        return Cache::remember($cacheKey, self::TTL_VERY_LONG, fn (): array => $callback());
    }

    /**
     * Cache theme manifest
     *
     * @param  callable(): array<string, mixed>  $callback
     * @return array<string, mixed>
     */
    public function rememberManifest(Theme $theme, callable $callback): array
    {
        $cacheKey = self::PREFIX_MANIFEST.$theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_VERY_LONG,
                fn (): array => $callback()
            );
        }

        return Cache::remember($cacheKey, self::TTL_VERY_LONG, fn (): array => $callback());
    }

    /**
     * Cache template list
     *
     * @param  callable(): array<int, string>  $callback
     * @return array<int, string>
     */
    public function rememberTemplates(Theme $theme, callable $callback): array
    {
        $cacheKey = self::PREFIX_TEMPLATES.$theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_LONG,
                fn (): array => $callback()
            );
        }

        return Cache::remember($cacheKey, self::TTL_LONG, fn (): array => $callback());
    }

    /**
     * Cache partial list
     *
     * @return mixed
     */
    public function rememberPartials(Theme $theme, callable $callback)
    {
        $cacheKey = self::PREFIX_PARTIALS.$theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_LONG,
                fn () => $callback()
            );
        }

        return Cache::remember($cacheKey, self::TTL_LONG, fn () => $callback());
    }

    /**
     * Cache layout list
     *
     * @return mixed
     */
    public function rememberLayouts(Theme $theme, callable $callback)
    {
        $cacheKey = self::PREFIX_LAYOUTS.$theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_LONG,
                fn () => $callback()
            );
        }

        return Cache::remember($cacheKey, self::TTL_LONG, fn () => $callback());
    }

    /**
     * Cache widget area
     *
     * @return mixed
     */
    public function rememberWidgetArea(string $location, callable $callback)
    {
        $cacheKey = self::PREFIX_WIDGETS.$location;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', 'widgets', "widgets.{$location}"])->remember(
                $cacheKey,
                self::TTL_MEDIUM,
                fn () => $callback()
            );
        }

        return Cache::remember($cacheKey, self::TTL_MEDIUM, fn () => $callback());
    }

    /**
     * Cache processed shortcodes
     *
     * @return mixed
     */
    public function rememberShortcode(string $content, callable $callback)
    {
        $cacheKey = self::PREFIX_SHORTCODES.md5($content);

        return Cache::remember($cacheKey, self::TTL_MEDIUM, fn () => $callback());
    }

    /**
     * Clear theme cache
     */
    public function clearTheme(Theme $theme): void
    {
        // Always drop active-theme keys (including API payload); not tag-scoped and must clear
        // even if tagged flush below fails (e.g. Redis unavailable while config says redis).
        foreach (['frontend', 'admin', 'email'] as $type) {
            try {
                Cache::forget(self::PREFIX_ACTIVE.$type);
                Cache::forget(self::PREFIX_ACTIVE_API_PAYLOAD.$type);
            } catch (\Exception $e) {
                Log::warning('Failed to forget active theme cache key: '.$e->getMessage());
            }
        }

        try {
            if ($this->tagsSupported()) {
                // Clear specific theme caches using tags
                Cache::tags(["theme.{$theme->id}"])->flush();
            } else {
                $this->forgetThemeScopedKeys((string) $theme->id);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear theme cache: '.$e->getMessage());
        }
    }

    /**
     * Clear all theme caches
     */
    public function clearAll(): void
    {
        foreach (['frontend', 'admin', 'email'] as $type) {
            try {
                Cache::forget(self::PREFIX_ACTIVE.$type);
                Cache::forget(self::PREFIX_ACTIVE_API_PAYLOAD.$type);
            } catch (\Exception $e) {
                Log::warning('Failed to forget active theme cache key: '.$e->getMessage());
            }
        }

        try {
            if ($this->tagsSupported()) {
                Cache::tags(['theme'])->flush();
            } else {
                foreach (Theme::query()->cursor() as $t) {
                    $this->forgetThemeScopedKeys((string) $t->id);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear all theme caches: '.$e->getMessage());
        }
    }

    /**
     * Clear widget cache
     */
    public function clearWidgets(?string $location = null): void
    {
        try {
            if ($this->tagsSupported()) {
                if ($location) {
                    Cache::tags(["widgets.{$location}"])->flush();
                } else {
                    Cache::tags(['widgets'])->flush();
                }
            } elseif ($location) {
                // Fallback: clear widget cache by key
                Cache::forget(self::PREFIX_WIDGETS.$location);
            } else {
                // Can't efficiently clear all widgets without tags, so just log
                Log::info('Widget cache clear requested but tags not supported');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear widget cache: '.$e->getMessage());
        }
    }

    /**
     * Forget per-theme cache keys (non–tag-aware drivers).
     */
    protected function forgetThemeScopedKeys(string $themeId): void
    {
        try {
            Cache::forget(self::PREFIX_SETTINGS.$themeId);
            Cache::forget(self::PREFIX_ASSETS.$themeId);
            Cache::forget(self::PREFIX_MANIFEST.$themeId);
            Cache::forget(self::PREFIX_TEMPLATES.$themeId);
            Cache::forget(self::PREFIX_PARTIALS.$themeId);
            Cache::forget(self::PREFIX_LAYOUTS.$themeId);
        } catch (\Exception $e) {
            Log::warning('Failed to forget theme-scoped cache keys: '.$e->getMessage());
        }
    }

    /**
     * Get cache statistics
     *
     * @return array{driver: string, tags_supported: bool, ttl_short: int, ttl_medium: int, ttl_long: int, ttl_very_long: int}
     */
    public function getStats(): array
    {
        $driver = config('cache.default');
        $driverStr = is_scalar($driver) ? (string) $driver : 'unknown';

        return [
            'driver' => $driverStr,
            'tags_supported' => $this->tagsSupported(),
            'ttl_short' => self::TTL_SHORT,
            'ttl_medium' => self::TTL_MEDIUM,
            'ttl_long' => self::TTL_LONG,
            'ttl_very_long' => self::TTL_VERY_LONG,
        ];
    }
}
