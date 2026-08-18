<?php

namespace Modules\Content\Layout\Helpers;

use Modules\Content\Layout\Models\Menu;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Layout\Services\ThemeService;

/**
 * Theme Helper Class
 * Provides static methods for interacting with the Vue-based theme system.
 * Handles theme data retrieval, settings, assets, and feature detection.
 */
class ThemeHelper
{
    protected static ?ThemeService $themeService = null;

    /**
     * Get ThemeService instance
     */
    protected static function getThemeService(): ThemeService
    {
        if (! self::$themeService instanceof ThemeService) {
            self::$themeService = app(ThemeService::class);
        }

        return self::$themeService;
    }

    /**
     * Get active theme
     */
    public static function activeTheme(string $type = 'frontend'): ?Theme
    {
        try {
            return self::getThemeService()->getActiveTheme($type);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Get theme setting
     */
    public static function setting(string $key, mixed $default = null, string $type = 'frontend'): mixed
    {
        try {
            $theme = self::getThemeService()->getActiveTheme($type);
            if ($theme instanceof Theme) {
                return self::getThemeService()->getThemeSetting($theme, $key, $default);
            }
        } catch (\Exception) {
            // Return default on error
        }

        return $default;
    }

    /**
     * Get theme asset URL
     */
    public static function asset(string $path, string $type = 'frontend'): ?string
    {
        try {
            $theme = self::getThemeService()->getActiveTheme($type);
            if ($theme && $theme->path) {
                $themePath = storage_path('app/themes/'.$theme->path);
                $assetPath = $themePath.'/'.ltrim($path, '/');

                if (file_exists($assetPath)) {
                    // Use /themes/{slug}/ path (via symlink)
                    return asset('themes/'.$theme->path.'/'.ltrim($path, '/'));
                }
            }
        } catch (\Exception) {
            // Return null on error
        }

        return null;
    }

    /**
     * Check if theme supports a feature
     */
    public static function supports(string $feature, string $type = 'frontend'): bool
    {
        try {
            $theme = self::getThemeService()->getActiveTheme($type);
            if ($theme && $theme->supports) {
                $supports = $theme->supports;

                return isset($supports[$feature]) && $supports[$feature] === true;
            }
        } catch (\Exception) {
            // Return false on error
        }

        return false;
    }

    /**
     * Get theme custom CSS
     */
    public static function customCss(string $type = 'frontend'): string
    {
        try {
            $theme = self::getThemeService()->getActiveTheme($type);
            if ($theme instanceof Theme) {
                return self::getThemeService()->getThemeCustomCss($theme);
            }
        } catch (\Exception) {
            // Return empty string on error
        }

        return '';
    }

    /**
     * Get menu by slug (for API use)
     */
    public static function getMenu(string $slug): ?Menu
    {
        try {
            return Menu::where('slug', $slug)
                ->where('is_active', true)
                ->with(['items' => function ($query): void {
                    $query->where('is_active', true)
                        ->whereNull('parent_id')
                        ->orderBy('sort_order');
                }, 'items.children' => function ($query): void {
                    $query->where('is_active', true)
                        ->orderBy('sort_order');
                }])
                ->first();
        } catch (\Exception) {
            return null;
        }
    }
}
