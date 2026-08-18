<?php

declare(strict_types=1);
use Modules\Content\Layout\Helpers\ThemeHelper;
use Modules\Content\Layout\Models\Menu;
use Modules\Content\Layout\Models\Theme;

/**
 * Global Helper Functions for Jejakawan Theme System
 * These functions provide a convenient API for interacting with the theme system.
 * Vue SPA handles rendering, so only data/configuration helpers are needed.
 */
if (! function_exists('theme')) {
    /**
     * Get active theme
     *
     * @param  string  $type  Theme type (frontend, admin, email)
     */
    function theme(string $type = 'frontend'): ?Theme
    {
        return ThemeHelper::activeTheme($type);
    }
}

if (! function_exists('theme_setting')) {
    /**
     * Get theme setting value
     *
     * @param  string  $key  Setting key
     * @param  mixed  $default  Default value if not found
     * @param  string  $type  Theme type
     */
    function theme_setting(string $key, $default = null, string $type = 'frontend'): mixed
    {
        return ThemeHelper::setting($key, $default, $type);
    }
}

if (! function_exists('theme_asset')) {
    /**
     * Get theme asset URL
     *
     * @param  string  $path  Relative path to asset
     * @param  string  $type  Theme type
     */
    function theme_asset(string $path, string $type = 'frontend'): ?string
    {
        return ThemeHelper::asset($path, $type);
    }
}

if (! function_exists('theme_supports')) {
    /**
     * Check if theme supports a feature
     *
     * @param  string  $feature  Feature name (e.g., 'custom_logo', 'menus')
     * @param  string  $type  Theme type
     */
    function theme_supports(string $feature, string $type = 'frontend'): bool
    {
        return ThemeHelper::supports($feature, $type);
    }
}

if (! function_exists('theme_custom_css')) {
    /**
     * Get theme custom CSS
     *
     * @param  string  $type  Theme type
     */
    function theme_custom_css(string $type = 'frontend'): string
    {
        return ThemeHelper::customCss($type);
    }
}

if (! function_exists('theme_menu')) {
    /**
     * Get theme menu data (for API/Vue consumption)
     *
     * @param  string  $slug  Menu slug
     */
    function theme_menu(string $slug): ?Menu
    {
        return ThemeHelper::getMenu($slug);
    }
}
