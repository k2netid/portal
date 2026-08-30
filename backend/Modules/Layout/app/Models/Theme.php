<?php

namespace Modules\Layout\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Layout\Database\Factories\ThemeFactory;
use Modules\Layout\Services\ThemeCacheService;
use Modules\Layout\Support\ThemeViews;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string $path
 * @property string $source
 * @property string|null $bundle_url
 * @property string|null $parent_theme
 * @property string $version
 * @property string|null $description
 * @property string|null $author
 * @property string|null $author_url
 * @property string|null $license
 * @property string|null $preview_image
 * @property array<string, mixed>|null $settings
 * @property string|null $custom_css
 * @property array<string, mixed>|null $dependencies
 * @property array<string|int, mixed>|null $supports
 * @property bool $is_active
 * @property string $status
 * @property string|null $update_url
 * @property bool $auto_update
 * @property string|null $requires_publishing_version
 * @property Carbon|null $last_updated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array<string, mixed>|null $manifest
 * @property array<string, mixed> $assets
 */
class Theme extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'lay_themes';

    /** @use HasFactory<ThemeFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ThemeFactory
    {
        return ThemeFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'type',
        'path',
        'source',
        'bundle_url',
        'parent_theme',
        'version',
        'description',
        'author',
        'author_url',
        'license',
        'preview_image',
        'settings',
        'custom_css',
        'dependencies',
        'supports',
        'is_active',
        'status',
        'update_url',
        'auto_update',
        'requires_publishing_version',
        'last_updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'dependencies' => 'array',
        'supports' => 'array',
        'is_active' => 'boolean',
        'auto_update' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Preferred slug when auto-activating a frontend theme (CMS reference theme).
     */
    public const DEFAULT_FRONTEND_SLUG = 'janari';

    /**
     * Get active theme by type
     * Auto-activates default theme if no theme is active
     */
    public static function getActiveTheme(string $type = 'frontend'): ?self
    {
        $activeTheme = self::query()
            ->where('is_active', true)
            ->where('type', $type)
            ->where('status', 'active')
            ->first();

        if (! $activeTheme) {
            $defaultTheme = null;
            // Janari is the CMS reference theme (builder + fork contract); Sarangenge is optional alternate.
            foreach ([self::DEFAULT_FRONTEND_SLUG, 'sarangenge', 'default'] as $slug) {
                $defaultTheme = self::query()
                    ->where('type', $type)
                    ->where('slug', $slug)
                    ->first();
                if ($defaultTheme) {
                    break;
                }
            }

            if (! $defaultTheme) {
                $defaultTheme = self::query()
                    ->where('type', $type)
                    ->orderBy('id')
                    ->first();
            }

            if ($defaultTheme) {
                try {
                    $defaultTheme->update([
                        'is_active' => true,
                        'status' => 'active',
                    ]);
                    Cache::forget(ThemeCacheService::PREFIX_ACTIVE.$type);

                    return $defaultTheme->fresh();
                } catch (\Exception $e) {
                    \Log::warning('Failed to auto-activate default theme: '.$e->getMessage());
                }
            }
        }

        return $activeTheme;
    }

    /**
     * Activate this theme
     */
    public function activate(): bool
    {
        // Validate theme before activation (warn but don't block)
        $errors = $this->validate();
        if ($errors !== []) {
            // Log warnings but allow activation
            \Log::warning("Theme '{$this->name}' has validation warnings but will be activated", [
                'theme_id' => $this->id,
                'errors' => $errors,
            ]);

            // Only block if critical errors (invalid JSON)
            $criticalErrors = array_filter($errors, fn ($error) => str_contains((string) $error, 'Invalid theme.json format'));

            if ($criticalErrors !== []) {
                throw new \Exception("Theme '{$this->name}' is invalid and cannot be activated: ".implode(', ', $criticalErrors));
            }
        }

        // Deactivate all other themes of the same type
        self::where('id', '!=', $this->id)
            ->where('type', $this->type)
            ->update(['is_active' => false]);

        // Activate this theme
        $this->update([
            'is_active' => true,
            'status' => 'active',
        ]);

        // Clear cache
        Cache::forget(ThemeCacheService::PREFIX_ACTIVE.$this->type);

        return true;
    }

    /**
     * Deactivate this theme
     */
    public function deactivate(): bool
    {
        $this->update(['is_active' => false]);
        Cache::forget(ThemeCacheService::PREFIX_ACTIVE.$this->type);

        return true;
    }

    /**
     * Get theme path
     */
    public function getThemePath(): string
    {
        if (($this->source ?? 'bundled') === 'uploaded') {
            return storage_path('app/public/themes').DIRECTORY_SEPARATOR.$this->slug;
        }

        return ThemeViews::pathForSlug($this->slug);
    }

    /**
     * Get theme public path (virtual path for assets)
     */
    public function getPublicPath(): string
    {
        return "themes/{$this->slug}";
    }

    /**
     * Check if theme has parent
     */
    public function hasParent(): bool
    {
        return ! empty($this->parent_theme);
    }

    /**
     * Get parent theme
     */
    public function getParent(): ?self
    {
        if (! $this->hasParent()) {
            return null;
        }

        return self::where('slug', $this->parent_theme)->first();
    }

    /**
     * Check if theme supports a feature
     */
    public function supports(string $feature): bool
    {
        if (! $this->supports) {
            return false;
        }

        return in_array($feature, $this->supports) ||
               (isset($this->supports[$feature]) && $this->supports[$feature] === true);
    }

    /**
     * Get theme setting
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        if (! is_array($this->settings)) {
            return $default;
        }

        return $this->settings[$key] ?? $default;
    }

    /**
     * Set theme setting
     */
    public function setSetting(string $key, mixed $value): void
    {
        /** @var array<string, mixed> $settings */
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }

    /**
     * Validate theme structure
     *
     * @return array<int, string>
     */
    public function validate(): array
    {
        /** @var array<int, string> $errors */
        $errors = [];
        $themePath = $this->getThemePath();

        // Check if theme directory exists
        if (! is_dir($themePath)) {
            $errors[] = "Theme directory does not exist: {$themePath}";

            return $errors;
        }

        // Check for theme.json
        $manifestPath = "{$themePath}/theme.json";
        if (! file_exists($manifestPath)) {
            $errors[] = 'Theme manifest (theme.json) not found';
        } else {
            // Validate manifest
            $content = file_get_contents($manifestPath);
            if ($content === false) {
                $errors[] = 'Could not read theme.json';
            } else {
                $manifest = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errors[] = 'Invalid theme.json format: '.json_last_error_msg();
                }
            }
        }

        // For code-first Vue themes, assets directory is optional.
        // Many themes are fully component-driven and ship no static assets folder.
        if (! is_dir("{$themePath}/assets")) {
            \Log::info("Theme '{$this->name}' does not have an assets directory");
        }

        // Check for Vue components directory (optional but recommended)
        if (! is_dir("{$themePath}/components")) {
            // Not an error, just a warning
            \Log::info("Theme '{$this->name}' does not have a components directory");
        }

        // Update status based on validation
        if (empty($errors)) {
            $this->update(['status' => 'active']);
        } else {
            $this->update(['status' => 'broken']);
        }

        return $errors;
    }

    /**
     * Get theme manifest
     *
     * @return array<string, mixed>|null
     */
    public function getManifest(): ?array
    {
        $manifestPath = "{$this->getThemePath()}/theme.json";

        if (! file_exists($manifestPath)) {
            return null;
        }

        $content = file_get_contents($manifestPath);
        if ($content === false) {
            return null;
        }

        $manifest = json_decode($content, true);

        /** @var array<string, mixed>|null $manifest */
        return is_array($manifest) ? $manifest : null;
    }

    /**
     * Check if theme has updates available
     */
    public function hasUpdate(): bool
    {
        // TODO: Implement update check logic
        return false;
    }

    /**
     * Get CSS assets
     *
     * @return array<int, string>
     */
    public function getCssAssets(): array
    {
        $assets = [];
        $themePath = $this->getThemePath();
        $cssDir = "{$themePath}/assets/css";

        if (is_dir($cssDir)) {
            $files = glob("{$cssDir}/*.css");
            if (is_array($files)) {
                foreach ($files as $file) {
                    $assets[] = basename($file);
                }
            }
        }

        return $assets;
    }

    /**
     * Get JS assets
     *
     * @return array<int, string>
     */
    public function getJsAssets(): array
    {
        $assets = [];
        $themePath = $this->getThemePath();
        $jsDir = "{$themePath}/assets/js";

        if (is_dir($jsDir)) {
            $files = glob("{$jsDir}/*.js");
            if (is_array($files)) {
                foreach ($files as $file) {
                    $assets[] = basename($file);
                }
            }
        }

        return $assets;
    }

    /**
     * Scope: Get themes by type
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        if ($type === 'all') {
            return $query;
        }

        return $query->where('type', $type);
    }

    /**
     * Scope: Get active themes
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get themes by status
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // =====================================================
    // VUE SPA METHODS (Added for Vue-based themes)
    // =====================================================

    /**
     * Check if theme has Vue components
     */
    public function hasVueComponents(): bool
    {
        $themePath = $this->getThemePath();
        $componentsDir = "{$themePath}/components";

        if (! is_dir($componentsDir)) {
            return false;
        }

        $files = glob("{$componentsDir}/*");

        return is_array($files) && count($files) > 0;
    }

    /**
     * Get Vue components directory path
     */
    public function getVueComponentsPath(): ?string
    {
        $themePath = $this->getThemePath();
        $componentsDir = "{$themePath}/components";

        return is_dir($componentsDir) ? $componentsDir : null;
    }

    /**
     * Get components manifest from theme.json
     *
     * @return array<string, mixed>
     */
    public function getComponentManifest(): array
    {
        $manifest = $this->getManifest();

        /** @var array<string, mixed> $components */
        $components = $manifest['components'] ?? [];

        return $components;
    }

    /**
     * Get composables directory path
     */
    public function getComposablesPath(): ?string
    {
        $themePath = $this->getThemePath();
        $composablesDir = "{$themePath}/composables";

        return is_dir($composablesDir) ? $composablesDir : null;
    }

    /**
     * Get theme configuration
     *
     * @return array{settings_schema: array<mixed>, supports: array<mixed>, menus: array<mixed>, components: array<mixed>}
     */
    public function getThemeConfig(): array
    {
        $manifest = $this->getManifest() ?? [];

        return [
            'settings_schema' => is_array($manifest['settings_schema'] ?? null) ? $manifest['settings_schema'] : [],
            'supports' => is_array($manifest['supports'] ?? null) ? $manifest['supports'] : [],
            'menus' => is_array($manifest['menus'] ?? null) ? $manifest['menus'] : [],
            'components' => is_array($manifest['components'] ?? null) ? $manifest['components'] : [],
        ];
    }

    /**
     * Check if theme is Vue-based
     */
    public function isVueBased(): bool
    {
        $manifest = $this->getManifest();

        return isset($manifest['framework']) && $manifest['framework'] === 'vue';
    }
}
