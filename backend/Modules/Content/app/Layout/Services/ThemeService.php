<?php

namespace Modules\Content\Layout\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Layout\Support\ThemeViews;
use Modules\Core\System\Contracts\LayoutRegistryInterface;
use Modules\Core\System\Models\Plugin;

class ThemeService
{
    protected ?ThemeHooksService $hooks = null;

    protected ThemeCacheService $cache;

    public function __construct(?ThemeHooksService $hooks = null, ?ThemeCacheService $cache = null)
    {
        $this->hooks = $hooks ?? app(ThemeHooksService::class);
        $this->cache = $cache ?? app(ThemeCacheService::class);
    }

    /**
     * Get active theme by type (with cache)
     * Auto-activates default theme if no theme is active
     */
    public function getActiveTheme(string $type = 'frontend'): ?Theme
    {
        return $this->cache->getActiveTheme($type, fn () => Theme::getActiveTheme($type));
    }

    /**
     * Get validated menu locations from theme manifest
     *
     * @return list<string>
     */
    public function getMenuLocations(Theme $theme): array
    {
        $manifest = $theme->getManifest();
        if ($manifest && isset($manifest['menus']) && is_array($manifest['menus'])) {
            $keys = array_keys($manifest['menus']);

            return array_values(array_map(static fn (int|string $key): string => (string) $key, $keys));
        }

        // Check parent theme if exists
        if ($theme->hasParent()) {
            $parent = $theme->getParent();
            if ($parent instanceof Theme) {
                return $this->getMenuLocations($parent);
            }
        }

        return [];
    }

    /**
     * Get validated widget locations from theme manifest
     *
     * @return list<string>
     */
    public function getWidgetLocations(Theme $theme): array
    {
        $manifest = $theme->getManifest();
        if ($manifest && isset($manifest['widgets']) && is_array($manifest['widgets'])) {
            $keys = array_keys($manifest['widgets']);

            return array_values(array_map(static fn (int|string $key): string => (string) $key, $keys));
        }

        // Check parent theme if exists
        if ($theme->hasParent()) {
            $parent = $theme->getParent();
            if ($parent instanceof Theme) {
                return $this->getWidgetLocations($parent);
            }
        }

        return [];
    }

    /**
     * Activate a theme
     */
    public function activateTheme(Theme $theme): bool
    {
        // Fire before activation hook
        if ($this->hooks instanceof ThemeHooksService) {
            $this->hooks->doAction('theme.before_activate', $theme);
        }

        // Validate theme before activation (warn but don't block if theme directory doesn't exist)
        $errors = $theme->validate();
        if (! empty($errors)) {
            // Log warnings but allow activation if theme is in database
            // Theme might be created manually or imported
            \Log::warning('Theme validation warnings: '.implode(', ', $errors), [
                'theme_id' => $theme->id,
                'theme_slug' => $theme->slug,
            ]);

            // Only block activation if critical errors (like invalid JSON)
            $criticalErrors = array_filter($errors, fn ($error) => str_contains((string) $error, 'Invalid theme.json format'));

            if ($criticalErrors !== []) {
                throw new \Exception('Theme validation failed: '.implode(', ', $criticalErrors));
            }
        }

        // Check dependencies (warn but don't block)
        if (! $this->checkDependencies($theme)) {
            \Log::warning('Theme dependencies not met', [
                'theme_id' => $theme->id,
                'theme_slug' => $theme->slug,
            ]);
            // Don't throw, just log warning
        }

        // Activate theme
        try {
            $theme->activate();
        } catch (\Exception $e) {
            // If activate() throws, check if it's validation error
            if (str_contains($e->getMessage(), 'invalid')) {
                // Allow activation even if validation fails (theme might be created manually)
                \Log::warning('Theme activation with validation warnings: '.$e->getMessage());
                // Manually activate
                Theme::where('id', '!=', $theme->id)
                    ->where('type', $theme->type)
                    ->update(['is_active' => false]);

                $theme->update([
                    'is_active' => true,
                    'status' => 'active',
                ]);

                Cache::forget(ThemeCacheService::PREFIX_ACTIVE.$theme->type);
                Cache::forget(ThemeCacheService::PREFIX_ACTIVE_API_PAYLOAD.$theme->type);
            } else {
                throw $e;
            }
        }

        // Clear relevant caches
        $this->clearThemeCache($theme);

        // Fire after activation hook
        if ($this->hooks instanceof ThemeHooksService) {
            $this->hooks->doAction('theme.activated', $theme);
        }

        // Sync locations with LayoutRegistry
        if (app()->bound(LayoutRegistryInterface::class)) {
            $registry = app(LayoutRegistryInterface::class);
            $registry->registerMenuLocations('publishing', $this->getMenuLocations($theme));
            $registry->registerWidgetLocations('publishing', $this->getWidgetLocations($theme));
        }

        return true;
    }

    /**
     * Deactivate a theme
     */
    public function deactivateTheme(Theme $theme): bool
    {
        $theme->deactivate();
        $this->clearThemeCache($theme);

        return true;
    }

    /**
     * Get theme setting with fallback
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function getThemeSetting(Theme $theme, string $key, $default = null)
    {
        // Check current theme settings
        $value = $theme->getSetting($key);
        if ($value !== null) {
            return $this->hooks instanceof ThemeHooksService ? $this->hooks->applyFilter('theme.setting', $value, $theme, $key, $default) : $value;
        }

        // Check parent theme if exists
        if ($theme->hasParent()) {
            $parent = $theme->getParent();
            if ($parent instanceof Theme) {
                $value = $parent->getSetting($key);
                if ($value !== null) {
                    return $this->hooks instanceof ThemeHooksService ? $this->hooks->applyFilter('theme.setting', $value, $theme, $key, $default) : $value;
                }
            }
        }

        // Check manifest defaults
        $manifest = $theme->getManifest();
        if (is_array($manifest) && isset($manifest['settings_schema']) && is_array($manifest['settings_schema'])) {
            $settingsSchema = $manifest['settings_schema'];
            if (isset($settingsSchema[$key]) && is_array($settingsSchema[$key])) {
                $schema = $settingsSchema[$key];
                if (isset($schema['default'])) {
                    $value = $schema['default'];

                    return $this->hooks instanceof ThemeHooksService ? $this->hooks->applyFilter('theme.setting', $value, $theme, $key, $default) : $value;
                }
            }
        }

        return $this->hooks instanceof ThemeHooksService ? $this->hooks->applyFilter('theme.setting', $default, $theme, $key, $default) : $default;
    }

    /**
     * Load theme assets (CSS/JS) with cache
     *
     * @return array{css: array<int, string>, js: array<int, string>}
     */
    public function loadThemeAssets(Theme $theme): array
    {
        if (! $theme->path) {
            return ['css' => [], 'js' => []];
        }

        /** @var array{css: array<int, string>, js: array<int, string>} $assets */
        $assets = $this->cache->rememberAssets($theme, function () use ($theme): array {
            $assets = [
                'css' => [],
                'js' => [],
            ];

            try {
                $themePath = $theme->getThemePath();

                // Load CSS files from manifest or directory
                $manifest = $theme->getManifest();
                if (is_array($manifest) && isset($manifest['assets']) && is_array($manifest['assets']) && isset($manifest['assets']['css']) && is_array($manifest['assets']['css'])) {
                    // Use CSS files from manifest
                    foreach ($manifest['assets']['css'] as $cssFile) {
                        if (is_string($cssFile)) {
                            $assets['css'][] = "themes/{$theme->path}/{$cssFile}";
                        }
                    }
                } else {
                    // Fallback: scan directory
                    $cssDir = "{$themePath}/assets/css";
                    if (is_dir($cssDir)) {
                        $cssFiles = (array) glob("{$cssDir}/*.css");
                        foreach ($cssFiles as $file) {
                            $filename = basename((string) $file);
                            $assets['css'][] = "themes/{$theme->path}/assets/css/{$filename}";
                        }
                    }
                }

                // Load JS files from manifest or directory
                if (is_array($manifest) && isset($manifest['assets']) && is_array($manifest['assets']) && isset($manifest['assets']['js']) && is_array($manifest['assets']['js'])) {
                    // Use JS files from manifest
                    foreach ($manifest['assets']['js'] as $jsFile) {
                        if (is_string($jsFile)) {
                            $assets['js'][] = "themes/{$theme->path}/{$jsFile}";
                        }
                    }
                } else {
                    // Fallback: scan directory
                    $jsDir = "{$themePath}/assets/js";
                    if (is_dir($jsDir)) {
                        $jsFiles = (array) glob("{$jsDir}/*.js");
                        foreach ($jsFiles as $file) {
                            $filename = basename((string) $file);
                            $assets['js'][] = "themes/{$theme->path}/assets/js/{$filename}";
                        }
                    }
                }

                // Load parent theme assets if exists
                if ($theme->hasParent()) {
                    $parent = $theme->getParent();
                    if ($parent instanceof Theme) {
                        $parentAssets = $this->loadThemeAssets($parent);
                        $assets['css'] = array_merge($parentAssets['css'], $assets['css']);
                        $assets['js'] = array_merge($parentAssets['js'], $assets['js']);
                    }
                }

            } catch (\Exception $e) {
                \Log::warning('Failed to load theme assets: '.$e->getMessage());
                // Return empty assets on error
            }

            return $assets;
        });

        return $assets;
    }

    /**
     * Compile theme assets
     */
    // Asset compilation removed (Handled by Vite)

    /**
     * Validate theme structure
     *
     * @return array<int, string>
     */
    public function validateTheme(Theme $theme): array
    {
        return $theme->validate();
    }

    /**
     * Check theme dependencies
     */
    public function checkDependencies(Theme $theme): bool
    {
        // Check dependencies
        if (empty($theme->dependencies)) {
            return true;
        }

        // Check required themes
        if (isset($theme->dependencies['themes']) && is_iterable($theme->dependencies['themes'])) {
            foreach ($theme->dependencies['themes'] as $requiredTheme) {
                if (! is_string($requiredTheme)) {
                    continue;
                }
                $parent = Theme::where('slug', $requiredTheme)->first();
                if (! $parent || ! $parent->is_active) {
                    return false;
                }
            }
        }

        // Check required plugins
        if (isset($theme->dependencies['plugins']) && is_iterable($theme->dependencies['plugins'])) {
            foreach ($theme->dependencies['plugins'] as $requiredPlugin) {
                if (! is_string($requiredPlugin)) {
                    continue;
                }
                $plugin = Plugin::where('slug', $requiredPlugin)->first();
                if (! $plugin || ! $plugin->is_active) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get theme custom CSS
     */
    public function getThemeCustomCss(Theme $theme): string
    {
        $customCss = $theme->getAttribute('custom_css');
        $css = is_string($customCss) ? $customCss : '';

        // Add parent theme custom CSS if exists
        if ($theme->hasParent()) {
            $parent = $theme->getParent();
            if ($parent instanceof Theme) {
                $parentCustomCss = $parent->getAttribute('custom_css');
                $parentCss = is_string($parentCustomCss) ? $parentCustomCss : '';
                if ($parentCss !== '' && $parentCss !== '0') {
                    $css = $parentCss."\n\n".$css;
                }
            }
        }

        return $css;
    }

    /**
     * Apply theme CSS variables
     */
    public function getThemeCssVariables(Theme $theme): string
    {
        $variables = [];
        $manifest = $theme->getManifest();

        if ($manifest && isset($manifest['settings_schema']) && is_iterable($manifest['settings_schema'])) {
            foreach ($manifest['settings_schema'] as $key => $setting) {
                if (is_array($setting) && isset($setting['type']) && $setting['type'] === 'color') {
                    $default = $setting['default'] ?? null;
                    $value = $this->getThemeSetting($theme, (string) $key, $default);
                    if ($value !== null) {
                        $cssKey = '--theme-'.str_replace('_', '-', (string) $key);
                        $valueStr = is_scalar($value) ? (string) $value : '';
                        $variables[] = "{$cssKey}: {$valueStr};";
                    }
                }
            }
        }

        if ($variables === []) {
            return '';
        }

        return ':root {'."\n  ".implode("\n  ", $variables)."\n}";
    }

    /**
     * Clear theme cache
     */
    public function clearThemeCache(?Theme $theme = null): void
    {
        if ($theme instanceof Theme) {
            $this->cache->clearTheme($theme);
        } else {
            $this->cache->clearAll();
        }
    }

    /**
     * Get theme directory path
     */
    public function getThemeDirectory(): string
    {
        return ThemeViews::rootPath();
    }

    /**
     * Ensure theme directory exists
     */
    public function ensureThemeDirectory(): bool
    {
        $dir = $this->getThemeDirectory();

        if (! is_dir($dir)) {
            return File::makeDirectory($dir, 0755, true);
        }

        return true;
    }

    /**
     * Scan themes directory and register themes
     *
     * @return array<int, Theme>
     */
    public function scanThemes(): array
    {
        $themes = [];

        $customDir = $this->getThemeDirectory();
        $roots = [];
        if ($customDir !== ThemeViews::rootPath()) {
            $roots[] = ['path' => $customDir, 'source' => 'bundled'];
        } else {
            $roots = ThemeViews::scanRootPaths();
        }

        foreach ($roots as $root) {
            $themesDir = $root['path'];
            $source = $root['source'];

            if (! is_dir($themesDir)) {
                continue;
            }

            foreach (File::directories($themesDir) as $dir) {
                $slug = basename((string) $dir);
                $manifestPath = "{$dir}/theme.json";
                if (! file_exists($manifestPath)) {
                    continue;
                }

                try {
                    /** @var array<string, mixed> $manifest */
                    $manifest = json_decode((string) file_get_contents($manifestPath), true);

                    if (! $manifest) {
                        continue;
                    }

                    $dependencies = $manifest['dependencies'] ?? null;
                    $supports = $manifest['supports'] ?? null;
                    $requires = $manifest['requires'] ?? null;

                    $bundleUrl = null;
                    if ($source === 'uploaded') {
                        $bundleFile = "{$dir}/theme.esm.js";
                        if (file_exists($bundleFile)) {
                            $bundleUrl = '/storage/themes/'.$slug.'/theme.esm.js';
                        }
                    }

                    $theme = Theme::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => is_scalar($vName = $manifest['name'] ?? $slug) ? (string) $vName : $slug,
                            'type' => is_scalar($vType = $manifest['type'] ?? 'frontend') ? (string) $vType : 'frontend',
                            'path' => $slug,
                            'source' => $source,
                            'bundle_url' => $bundleUrl,
                            'version' => is_scalar($vVer = $manifest['version'] ?? '1.0.0') ? (string) $vVer : '1.0.0',
                            'description' => is_scalar($vDesc = $manifest['description'] ?? null) ? (string) $vDesc : '',
                            'author' => is_scalar($vAuth = $manifest['author'] ?? null) ? (string) $vAuth : '',
                            'author_url' => is_scalar($vAuthUrl = $manifest['author_url'] ?? null) ? (string) $vAuthUrl : '',
                            'license' => is_scalar($vLic = $manifest['license'] ?? null) ? (string) $vLic : '',
                            'parent_theme' => is_scalar($vParent = $manifest['parent_theme'] ?? null) ? (string) $vParent : null,
                            'dependencies' => is_array($dependencies) ? $dependencies : null,
                            'supports' => is_array($supports) ? $supports : null,
                            'requires_publishing_version' => is_array($requires) ? ($requires['cms_version'] ?? null) : null,
                            'status' => 'active',
                        ]
                    );

                    $themes[] = $theme;
                } catch (\Exception $e) {
                    \Log::error("Failed to load theme {$slug} from {$source}: ".$e->getMessage());
                }
            }
        }

        return $themes;
    }

    /**
     * Get default settings schema for themes without manifest
     * Optimized for "Janari" theme with modern UI/UX
     *
     * @return array<string, array<string, mixed>>
     */
    private const THEME_DATA_BINDINGS_KEY = 'theme_data_bindings';

    /**
     * Build settings for a full customizer publish: drop stale keys no longer in manifest schema.
     *
     * @param  array<string, mixed>  $settingsInput
     * @return array<string, mixed>
     */
    public function settingsForCustomizationPublish(Theme $theme, array $settingsInput): array
    {
        $config = $theme->getThemeConfig();
        /** @var array<string, mixed> $schema */
        $schema = $config['settings_schema'];
        $allowed = array_fill_keys(array_keys($schema), true);
        $allowed[self::THEME_DATA_BINDINGS_KEY] = true;

        $existing = is_array($theme->settings) ? $theme->settings : [];
        $next = [];

        foreach ($settingsInput as $key => $value) {
            $keyStr = (string) $key;
            if (isset($allowed[$keyStr]) || str_starts_with($keyStr, '_')) {
                $next[$keyStr] = $value;
            }
        }

        foreach ($existing as $key => $value) {
            $keyStr = (string) $key;
            if (str_starts_with($keyStr, '_') && ! array_key_exists($keyStr, $next)) {
                $next[$keyStr] = $value;
            }
        }

        return $this->normalizeThemeDataBindingsInSettings($next);
    }

    /**
     * Normalize slot binding shapes inside `theme_data_bindings` for persistence/API output.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function normalizeThemeDataBindingsInSettings(array $settings): array
    {
        $bindings = $settings[self::THEME_DATA_BINDINGS_KEY] ?? null;
        if (! is_array($bindings)) {
            return $settings;
        }

        /** @var array<string, array<int, string>> $componentAliases */
        $componentAliases = [
            'majors' => ['programs'],
            'partners' => ['partner'],
        ];

        /** @var array<string, array<string, array<int, string>>> $slotAliases */
        $slotAliases = [
            'majors' => ['programs' => ['default']],
            'stats' => ['counters' => ['default']],
            'testimonials' => ['items' => ['default']],
            'partners' => ['partners' => ['default']],
        ];

        /** @var array<string, mixed> $normalized */
        $normalized = [];

        foreach ($bindings as $componentId => $componentConfig) {
            if (! is_array($componentConfig)) {
                continue;
            }

            $targetComponentId = $componentId;
            foreach ($componentAliases as $canonical => $aliases) {
                if ($componentId === $canonical || in_array($componentId, $aliases, true)) {
                    $targetComponentId = $canonical;
                    break;
                }
            }

            /** @var array<string, mixed> $existingComponent */
            $existingComponent = isset($normalized[$targetComponentId]) && is_array($normalized[$targetComponentId])
                ? $normalized[$targetComponentId]
                : ['slots' => []];
            if (! isset($existingComponent['slots']) || ! is_array($existingComponent['slots'])) {
                $existingComponent['slots'] = [];
            }
            $slotsInput = $componentConfig['slots'] ?? [];
            if (! is_array($slotsInput)) {
                $slotsInput = [];
            }

            foreach ($slotsInput as $slotId => $slotConfig) {
                if (! is_array($slotConfig)) {
                    continue;
                }

                $targetSlotId = $slotId;
                $slotMap = $slotAliases[$targetComponentId] ?? [];
                foreach ($slotMap as $canonicalSlot => $aliases) {
                    if ($slotId === $canonicalSlot || in_array($slotId, $aliases, true)) {
                        $targetSlotId = $canonicalSlot;
                        break;
                    }
                }

                if (! isset($slotConfig['pageSlug']) && isset($slotConfig['pageId']) && is_scalar($slotConfig['pageId'])) {
                    $slotConfig['pageSlug'] = (string) $slotConfig['pageId'];
                }

                if (! isset($slotConfig['propMapping']) || ! is_array($slotConfig['propMapping'])) {
                    $slotConfig['propMapping'] = [];
                }

                $existingSlot = $existingComponent['slots'][$targetSlotId] ?? null;
                $existingComponent['slots'][$targetSlotId] = array_merge(
                    is_array($existingSlot) ? $existingSlot : [],
                    $slotConfig
                );
            }

            $normalized[$targetComponentId] = array_merge($componentConfig, $existingComponent);
        }

        $settings[self::THEME_DATA_BINDINGS_KEY] = $normalized;

        return $settings;
    }

    public function normalizeThemeDataBindings(Theme $theme): void
    {
        $settings = is_array($theme->settings) ? $theme->settings : [];
        $theme->settings = $this->normalizeThemeDataBindingsInSettings($settings);
    }

    /**
     * Cached JSON-serializable payload for public GET /themes/active (same shape as Theme Eloquent + assets + manifest).
     *
     * @return array<string, mixed>|null
     */
    public function getActiveThemePublicPayload(string $type): ?array
    {
        $theme = $this->getActiveTheme($type);
        if (! $theme instanceof Theme) {
            return null;
        }

        try {
            return Cache::remember(
                ThemeCacheService::PREFIX_ACTIVE_API_PAYLOAD.$type,
                ThemeCacheService::TTL_LONG,
                function () use ($type): array {
                    $fresh = $this->getActiveTheme($type);
                    if (! $fresh instanceof Theme) {
                        throw new \LogicException('no_active_theme');
                    }
                    $this->normalizeThemeDataBindings($fresh);
                    $assets = $this->loadThemeAssets($fresh);
                    $fresh->setAttribute('assets', $assets);
                    $fresh->setAttribute('manifest', $fresh->getManifest());

                    /** @var array<string, mixed> */
                    return $fresh->toArray();
                }
            );
        } catch (\LogicException $e) {
            if ($e->getMessage() === 'no_active_theme') {
                return null;
            }

            throw $e;
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getDefaultSettingsSchema(): array
    {
        return [
            // General Settings
            'site_title' => [
                'type' => 'text',
                'label' => 'Site Title',
                'description' => 'The main title of your website',
                'default' => 'Janari',
                'placeholder' => 'Enter site title',
                'category' => 'General',
            ],
            'site_tagline' => [
                'type' => 'text',
                'label' => 'Site Tagline',
                'description' => 'A short description or tagline for your site',
                'default' => 'Modern & Elegant Jejakawan Theme',
                'placeholder' => 'Enter tagline',
                'category' => 'General',
            ],
            'site_logo_url' => [
                'type' => 'url',
                'label' => 'Logo URL',
                'description' => 'URL to your site logo image',
                'default' => '',
                'placeholder' => 'https://example.com/logo.png',
                'category' => 'General',
            ],

            // Color Scheme
            'primary_color' => [
                'type' => 'color',
                'label' => 'Primary Color',
                'description' => 'The main brand color used throughout the theme',
                'default' => '#6366F1',
                'category' => 'Colors',
            ],
            'secondary_color' => [
                'type' => 'color',
                'label' => 'Secondary Color',
                'description' => 'The secondary brand color for accents',
                'default' => '#8B5CF6',
                'category' => 'Colors',
            ],
            'accent_color' => [
                'type' => 'color',
                'label' => 'Accent Color',
                'description' => 'Color for highlights and call-to-action elements',
                'default' => '#EC4899',
                'category' => 'Colors',
            ],
            'background_color' => [
                'type' => 'color',
                'label' => 'Background Color',
                'description' => 'Main background color',
                'default' => '#FFFFFF',
                'category' => 'Colors',
            ],
            'text_color' => [
                'type' => 'color',
                'label' => 'Text Color',
                'description' => 'Main text color',
                'default' => '#1F2937',
                'category' => 'Colors',
            ],

            // Typography
            'heading_font' => [
                'type' => 'select',
                'label' => 'Heading Font',
                'description' => 'Font family for headings',
                'default' => 'Inter',
                'options' => [
                    ['value' => 'Inter', 'label' => 'Inter'],
                    ['value' => 'Poppins', 'label' => 'Poppins'],
                    ['value' => 'Roboto', 'label' => 'Roboto'],
                    ['value' => 'Open Sans', 'label' => 'Open Sans'],
                    ['value' => 'Lato', 'label' => 'Lato'],
                    ['value' => 'Montserrat', 'label' => 'Montserrat'],
                ],
                'category' => 'Typography',
            ],
            'body_font' => [
                'type' => 'select',
                'label' => 'Body Font',
                'description' => 'Font family for body text',
                'default' => 'Inter',
                'options' => [
                    ['value' => 'Inter', 'label' => 'Inter'],
                    ['value' => 'Poppins', 'label' => 'Poppins'],
                    ['value' => 'Roboto', 'label' => 'Roboto'],
                    ['value' => 'Open Sans', 'label' => 'Open Sans'],
                    ['value' => 'Lato', 'label' => 'Lato'],
                    ['value' => 'Source Sans Pro', 'label' => 'Source Sans Pro'],
                ],
                'category' => 'Typography',
            ],
            'font_size_base' => [
                'type' => 'number',
                'label' => 'Base Font Size',
                'description' => 'Base font size in pixels',
                'default' => 16,
                'min' => 12,
                'max' => 20,
                'step' => 1,
                'category' => 'Typography',
            ],

            // Layout
            'container_width' => [
                'type' => 'select',
                'label' => 'Container Width',
                'description' => 'Maximum width of content container',
                'default' => '1280px',
                'options' => [
                    ['value' => '1024px', 'label' => 'Small (1024px)'],
                    ['value' => '1280px', 'label' => 'Medium (1280px)'],
                    ['value' => '1536px', 'label' => 'Large (1536px)'],
                    ['value' => '100%', 'label' => 'Full Width'],
                ],
                'category' => 'Layout',
            ],
            'header_style' => [
                'type' => 'select',
                'label' => 'Header Style',
                'description' => 'Header layout style',
                'default' => 'centered',
                'options' => [
                    ['value' => 'centered', 'label' => 'Centered'],
                    ['value' => 'left', 'label' => 'Left Aligned'],
                    ['value' => 'minimal', 'label' => 'Minimal'],
                    ['value' => 'sticky', 'label' => 'Sticky Header'],
                ],
                'category' => 'Layout',
            ],
            'sidebar_enabled' => [
                'type' => 'checkbox',
                'label' => 'Enable Sidebar',
                'description' => 'Show sidebar on blog/content pages',
                'default' => false,
                'category' => 'Layout',
            ],

            // Footer
            'footer_text' => [
                'type' => 'textarea',
                'label' => 'Footer Text',
                'description' => 'Text displayed in the footer',
                'default' => '© 2024 Janari Theme. All rights reserved.',
                'rows' => 3,
                'category' => 'Footer',
            ],
            'footer_columns' => [
                'type' => 'number',
                'label' => 'Footer Columns',
                'description' => 'Number of footer columns (1-4)',
                'default' => 4,
                'min' => 1,
                'max' => 4,
                'step' => 1,
                'category' => 'Footer',
            ],
            'show_social_links' => [
                'type' => 'checkbox',
                'label' => 'Show Social Links',
                'description' => 'Display social media links in footer',
                'default' => true,
                'category' => 'Footer',
            ],

            // Blog/Content
            'posts_per_page' => [
                'type' => 'number',
                'label' => 'Posts Per Page',
                'description' => 'Number of posts to show per page',
                'default' => 9,
                'min' => 1,
                'max' => 50,
                'step' => 1,
                'category' => 'Content',
            ],
            'excerpt_length' => [
                'type' => 'number',
                'label' => 'Excerpt Length',
                'description' => 'Number of words in post excerpts',
                'default' => 30,
                'min' => 10,
                'max' => 100,
                'step' => 5,
                'category' => 'Content',
            ],
            'show_author' => [
                'type' => 'checkbox',
                'label' => 'Show Author',
                'description' => 'Display author information on posts',
                'default' => true,
                'category' => 'Content',
            ],
            'show_date' => [
                'type' => 'checkbox',
                'label' => 'Show Date',
                'description' => 'Display publication date on posts',
                'default' => true,
                'category' => 'Content',
            ],

            // Performance
            'lazy_load_images' => [
                'type' => 'checkbox',
                'label' => 'Lazy Load Images',
                'description' => 'Enable lazy loading for images to improve performance',
                'default' => true,
                'category' => 'Performance',
            ],
            'enable_animations' => [
                'type' => 'checkbox',
                'label' => 'Enable Animations',
                'description' => 'Enable smooth scroll animations',
                'default' => true,
                'category' => 'Performance',
            ],
        ];
    }
}
