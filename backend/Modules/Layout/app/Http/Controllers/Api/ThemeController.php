<?php

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Extension;
use Modules\Layout\Models\Theme;
use Modules\Layout\SampleData\ThemeSampleDataInstallOptions;
use Modules\Layout\SampleData\ThemeSampleDataOrchestrator;
use Modules\Layout\SampleData\ThemeSampleDataReader;
use Modules\Layout\Services\ThemePackageInstallService;
use Modules\Layout\Services\ThemeService;
use Modules\Layout\Support\ThemeViews;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ThemeController extends BaseApiController
{
    public function __construct(protected ThemeService $themeService)
    {
        $this->middleware('auth:sanctum')->except(['getActive']);
        $this->middleware('permission:manage themes')->except(['getActive']);
    }

    public function index(Request $request): JsonResponse
    {
        $typeRaw = $request->input('type', 'frontend');
        $type = is_string($typeRaw) ? $typeRaw : 'frontend';

        // Auto-discover bundled & uploaded themes from disk
        $this->themeService->scanThemes();

        $themes = Theme::query()
            ->ofType($type)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        // Attach manifest to each theme
        $themes->each(function ($theme): void {
            $theme->manifest = $theme->getManifest();
        });

        return $this->success($themes, 'Themes retrieved successfully');
    }

    // Store method removed (Themes are code-managed)

    public function show(Theme $theme): JsonResponse
    {
        $this->themeService->normalizeThemeDataBindings($theme);

        // Load theme assets
        $assets = $this->themeService->loadThemeAssets($theme);
        $theme->assets = $assets;

        // Load manifest if available
        $manifest = $theme->getManifest();
        if ($manifest) {
            $theme->manifest = $manifest;
        } else {
            // Provide default settings schema if no manifest
            $theme->manifest = [
                'name' => $theme->name,
                'version' => $theme->version ?? '1.0.0',
                'description' => $theme->description ?? '',
                'author' => $theme->author ?? '',
                'settings_schema' => $this->themeService->getDefaultSettingsSchema(),
            ];
        }

        return $this->success($theme, 'Theme retrieved successfully');
    }

    public function update(Request $request, Theme $theme): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:lay_themes,slug,'.$theme->id,
            'type' => 'sometimes|string|in:frontend,admin,email',
            'version' => 'nullable|string',
            'description' => 'nullable|string',
            'author' => 'nullable|string',
            'author_url' => 'nullable|url',
            'license' => 'nullable|string',
            'preview_image' => 'nullable|string',
            'settings' => 'nullable|array',
            'custom_css' => 'nullable|string|max:50000',
            'parent_theme' => 'nullable|string|exists:lay_themes,slug',
            'dependencies' => 'nullable|array',
            'supports' => 'nullable|array',
        ]);

        $theme->update($validated);

        // Clear cache after update
        $this->themeService->clearThemeCache($theme);

        return $this->success($theme, 'Theme updated successfully');
    }

    public function destroy(Theme $theme): JsonResponse
    {
        if ($theme->is_active) {
            return $this->validationError(
                ['theme' => ['Cannot delete active theme']],
                'Cannot delete active theme'
            );
        }

        $theme->delete();
        $this->themeService->clearThemeCache($theme);

        return $this->success(null, 'Theme deleted successfully');
    }

    public function activate(Theme $theme): JsonResponse
    {
        try {
            $this->themeService->activateTheme($theme);

            return $this->success([
                'theme' => $theme->fresh(),
            ], 'Theme activated successfully');
        } catch (\Exception $e) {
            Log::error('Theme activation failed', [
                'theme_id' => $theme->id,
                'theme_slug' => $theme->slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error($e->getMessage(), 422);
        }
    }

    public function deactivate(Theme $theme): JsonResponse
    {
        try {
            $this->themeService->deactivateTheme($theme);

            return $this->success([
                'theme' => $theme->fresh(),
            ], 'Theme deactivated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function getActive(Request $request): JsonResponse
    {
        if (! Extension::isProductActive('layout')) {
            return $this->success(null, 'No active theme found');
        }

        $profile = (bool) config('publishing.profile_public_theme_api', false);
        $t0 = $profile ? microtime(true) : 0.0;

        try {
            $typeRaw = $request->input('type', 'frontend');
            $type = is_string($typeRaw) ? $typeRaw : 'frontend';

            $payload = $this->themeService->getActiveThemePublicPayload($type);

            if ($payload === null) {
                return $this->success(null, 'No active theme found');
            }

            $etagSource = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $etag = '"'.sha1(is_string($etagSource) ? $etagSource : '').'"';
            $ifNoneMatch = $request->headers->get('If-None-Match');
            if (is_string($ifNoneMatch) && trim($ifNoneMatch) === $etag) {
                return response()->json(null, 304, [
                    'ETag' => $etag,
                    'Vary' => 'Accept-Encoding',
                ]);
            }

            $response = $this->success($payload, 'Active theme retrieved successfully');
            $response->headers->set('ETag', $etag);
            $response->headers->set('Vary', 'Accept-Encoding');

            $maxAgeRaw = config('publishing.public_active_theme_http_cache_max_age', 0);
            $maxAge = is_int($maxAgeRaw) ? $maxAgeRaw : (is_numeric($maxAgeRaw) ? (int) $maxAgeRaw : 0);
            $maxAge = max(0, $maxAge);
            if ($maxAge > 0 && $request->is('api/v1/ja/*')) {
                $response->headers->set(
                    'Cache-Control',
                    'public, max-age='.$maxAge.', stale-while-revalidate='.min(600, $maxAge * 5)
                );
            }

            if ($profile && $t0 > 0 && $request->is('api/v1/ja/*')) {
                $durMs = round((microtime(true) - $t0) * 1000, 2);
                $response->headers->set('Server-Timing', 'theme-active;dur='.$durMs);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to get active theme: '.$e->getMessage());

            // Return null instead of error for public endpoint
            return $this->success(null, 'Theme service unavailable');
        }
    }

    public function updateSettings(Request $request, Theme $theme): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        try {
            // Merge with existing settings instead of replacing
            $existingSettings = is_array($theme->settings) ? $theme->settings : [];
            $settingsInput = is_array($validated['settings']) ? $validated['settings'] : [];

            $newSettings = array_merge($existingSettings, $settingsInput);
            $newSettings = $this->themeService->normalizeThemeDataBindingsInSettings($newSettings);
            $this->syncThemeSettingsToGlobalSettings($newSettings);

            $theme->update(['settings' => $newSettings]);

            // Branding is now separate. Jejakawan brand_logo does not overwrite Core site_logo.
            // Favicon is now separate.

            $this->themeService->clearThemeCache($theme);

            return $this->success($theme->fresh(), 'Theme settings updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update theme settings: '.$e->getMessage(), [
                'theme_id' => $theme->id,
                'error' => $e->getTraceAsString(),
            ]);

            return $this->error('Failed to update theme settings: '.$e->getMessage(), 500);
        }
    }

    public function updateCustomCss(Request $request, Theme $theme): JsonResponse
    {
        $validated = $request->validate([
            'custom_css' => 'nullable|string',
        ]);

        $customCss = isset($validated['custom_css']) && is_string($validated['custom_css']) ? $validated['custom_css'] : '';
        $theme->update(['custom_css' => $customCss]);
        $this->themeService->clearThemeCache($theme);

        return $this->success($theme, 'Theme custom CSS updated successfully');
    }

    public function updateCustomization(Request $request, Theme $theme): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'custom_css' => 'nullable|string',
        ]);

        try {
            $settingsInput = is_array($validated['settings']) ? $validated['settings'] : [];
            $customCss = isset($validated['custom_css']) && is_string($validated['custom_css']) ? $validated['custom_css'] : '';

            $newSettings = $this->themeService->settingsForCustomizationPublish($theme, $settingsInput);
            $this->syncThemeSettingsToGlobalSettings($newSettings);

            \DB::transaction(function () use ($theme, $newSettings, $customCss): void {
                $theme->update([
                    'settings' => $newSettings,
                    'custom_css' => $customCss,
                ]);

                // Branding is now separate.
                // Favicon is now separate.
            });

            $this->themeService->clearThemeCache($theme);

            return $this->success($theme->fresh(), 'Theme customization updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update theme customization: '.$e->getMessage(), [
                'theme_id' => $theme->id,
                'error' => $e->getTraceAsString(),
            ]);

            return $this->error('Failed to update theme customization: '.$e->getMessage(), 500);
        }
    }

    public function validate(Theme $theme): JsonResponse
    {
        $errors = $this->themeService->validateTheme($theme);

        if ($errors === []) {
            return $this->success([
                'valid' => true,
                'theme' => $theme->fresh(),
            ], 'Theme is valid');
        }

        return $this->success([
            'valid' => false,
            'errors' => $errors,
            'theme' => $theme->fresh(),
        ], 'Theme validation completed');
    }

    // Legacy Blade methods removed

    protected function isExportAllowed(): bool
    {
        if (class_exists(\Modules\Core\System\Models\Setting::class)) {
            $settingAllowed = filter_var(\Modules\Core\System\Models\Setting::get('enable_theme_export', true), FILTER_VALIDATE_BOOLEAN);
            if (! $settingAllowed) {
                return false;
            }

            if (\Modules\Core\System\Models\Setting::get('license_type') === 'community') {
                return false;
            }
        }

        if (! app()->isProduction()) {
            return true;
        }

        if (class_exists(\Modules\Core\System\Services\LicenseService::class)) {
            return app(\Modules\Core\System\Services\LicenseService::class)->canUseFeature('theme_export');
        }

        return true;
    }

    public function uploadStatus(ThemePackageInstallService $installer): JsonResponse
    {
        return $this->success([
            'enabled' => $installer->isEnabled(),
            'export_enabled' => $this->isExportAllowed(),
            'max_zip_bytes' => config('layout.uploaded_themes.max_zip_bytes', 52_428_800),
            'storage_path' => 'storage/app/public/themes',
        ], 'Theme upload status');
    }

    public function install(Request $request, ThemePackageInstallService $installer): JsonResponse
    {
        if (! $installer->isEnabled()) {
            return $this->error('Uploaded themes are disabled on this installation.', 403);
        }

        $request->validate([
            'theme_zip' => 'required|file|mimes:zip|max:51200',
        ]);

        try {
            /** @var UploadedFile $file */
            $file = $request->file('theme_zip');
            $result = $installer->installFromZip($file);

            return $this->success([
                'theme' => $result['theme'],
                'warnings' => $result['warnings'],
            ], 'Theme package installed successfully', 201);
        } catch (\InvalidArgumentException $e) {
            return $this->validationError(['theme_zip' => [$e->getMessage()]], $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Theme install failed: '.$e->getMessage());

            return $this->error('Theme install failed: '.$e->getMessage(), 500);
        }
    }

    public function scan(): JsonResponse
    {
        try {
            $themes = $this->themeService->scanThemes();

            return $this->success([
                'themes' => $themes,
                'count' => count($themes),
            ], 'Themes scanned successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function getSetting(Theme $theme, Request $request): JsonResponse
    {
        $keyRaw = $request->input('key');
        $key = is_string($keyRaw) ? $keyRaw : '';
        $default = $request->input('default');

        if ($key === '' || $key === '0') {
            return $this->validationError(['key' => ['Key is required']], 'Key is required');
        }

        $value = $this->themeService->getThemeSetting($theme, $key, $default);

        return $this->success([
            'key' => $key,
            'value' => $value,
        ], 'Theme setting retrieved successfully');
    }

    public function export(Theme $theme): BinaryFileResponse|JsonResponse
    {
        if (! $this->isExportAllowed()) {
            return $this->error('Theme export is disabled on this installation.', 403);
        }

        $sourcePath = ThemeViews::pathForSlug($theme->slug);
        if (! is_dir($sourcePath) || ! is_file($sourcePath.'/theme.json')) {
            return $this->error("Theme directory or manifest not found for [{$theme->slug}].", 404);
        }

        try {
            $tempParent = storage_path('app/temp');
            if (! is_dir($tempParent)) {
                File::ensureDirectoryExists($tempParent, 0777, true);
                @chmod($tempParent, 0777);
            }
            $tempDir = $tempParent.'/theme-export-'.Str::random(16);
            File::ensureDirectoryExists($tempDir, 0777, true);
            @chmod($tempDir, 0777);
            $zipPath = $tempDir."/{$theme->slug}-theme.zip";

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return $this->error('Failed to create theme zip archive.', 500);
            }

        $files = File::allFiles($sourcePath);
        foreach ($files as $file) {
            $relative = $file->getRelativePathname();
            if (str_starts_with($relative, '.git') || str_ends_with($relative, '.DS_Store')) {
                continue;
            }
            $zip->addFile($file->getRealPath(), "{$theme->slug}/{$relative}");
        }

        $zip->close();

        return response()->download($zipPath, "{$theme->slug}-theme.zip")->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Theme export failed', ['theme' => $theme->slug, 'error' => $e->getMessage()]);
            return $this->error('Theme export failed: '.$e->getMessage(), 500);
        }
    }

    // =====================================================
    // VUE SPA ENDPOINTS (New methods for Vue themes)
    // =====================================================

    /**
     * Get active theme menu locations
     */
    public function locations(Request $request): JsonResponse
    {
        $typeRaw = $request->input('type', 'frontend');
        $type = is_string($typeRaw) ? $typeRaw : 'frontend';
        $theme = $this->themeService->getActiveTheme($type);

        if (! $theme instanceof Theme) {
            return $this->success([], 'No active theme found');
        }

        $locations = $this->themeService->getMenuLocations($theme);

        return $this->success($locations, 'Menu locations retrieved successfully');
    }

    /**
     * Get Vue components manifest
     */
    public function getComponents(Theme $theme): JsonResponse
    {
        try {
            $componentManifest = $theme->getComponentManifest();

            return $this->success([
                'components' => $componentManifest,
                'has_vue_components' => $theme->hasVueComponents(),
                'is_vue_based' => $theme->isVueBased(),
            ], 'Theme components retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get theme configuration
     */
    public function getConfig(Theme $theme): JsonResponse
    {
        try {
            $config = $theme->getThemeConfig();

            return $this->success($config, 'Theme configuration retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get theme composables
     */
    public function getComposables(Theme $theme): JsonResponse
    {
        try {
            $composablesPath = $theme->getComposablesPath();

            if (! $composablesPath) {
                return $this->success([
                    'has_composables' => false,
                    'message' => 'Theme does not have composables directory',
                ]);
            }

            /** @var list<string>|false $composableFiles */
            $composableFiles = glob("{$composablesPath}/*.js");
            $composables = $composableFiles ? array_map(basename(...), $composableFiles) : [];

            return $this->success([
                'has_composables' => true,
                'composables' => $composables,
            ], 'Theme composables retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function installSample(
        Request $request,
        Theme $theme,
        ThemeSampleDataReader $reader,
        ThemeSampleDataOrchestrator $orchestrator,
    ): JsonResponse {
        if (($theme->type ?? 'frontend') !== 'frontend') {
            return $this->validationError(
                ['theme' => ['Sample data is only available for frontend themes.']],
                'Invalid theme type'
            );
        }

        if (! $reader->hasBundle((string) $theme->slug)) {
            return $this->validationError(
                ['theme' => ['This theme has no sample-data bundle.']],
                'Sample data not available'
            );
        }

        $validated = $request->validate([
            'force' => 'sometimes|boolean',
            'only' => 'sometimes|string|max:120',
        ]);

        $onlyRaw = $validated['only'] ?? null;
        $onlyParts = is_string($onlyRaw) && trim($onlyRaw) !== ''
            ? array_map('trim', explode(',', strtolower($onlyRaw)))
            : null;

        $options = new ThemeSampleDataInstallOptions(
            force: (bool) ($validated['force'] ?? false),
            menus: $onlyParts === null || in_array('menus', $onlyParts, true),
            settings: $onlyParts === null || in_array('settings', $onlyParts, true),
            pages: $onlyParts === null || in_array('pages', $onlyParts, true),
            forms: $onlyParts === null || in_array('forms', $onlyParts, true),
        );

        try {
            $result = $orchestrator->install($theme->fresh() ?? $theme, $options);
        } catch (\Throwable $e) {
            Log::error('Theme sample data install failed', [
                'theme_slug' => $theme->slug,
                'error' => $e->getMessage(),
            ]);

            return $this->error($e->getMessage(), 422);
        }

        return $this->success($result->toArray(), 'Theme sample data installed successfully');
    }

    /**
     * Bidirectionally sync school identity settings back to global sys_settings.
     */
    private function syncThemeSettingsToGlobalSettings(array &$newSettings): void
    {
        $schoolName = $newSettings['school_name'] ?? $newSettings['site_title'] ?? null;
        if (is_string($schoolName) && trim($schoolName) !== '') {
            $trimmed = trim($schoolName);
            $newSettings['school_name'] = $trimmed;
            $newSettings['site_title'] = $trimmed;
            try {
                if (class_exists(\Modules\Core\System\Models\Setting::class)) {
                    \Modules\Core\System\Models\Setting::set('site_name', $trimmed, 'string', 'general');
                    \Modules\Core\System\Models\Setting::clearCache('site_name');
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to sync school_name to site_name: '.$e->getMessage());
            }
        }

        $schoolTagline = $newSettings['school_tagline'] ?? $newSettings['site_tagline'] ?? null;
        if (is_string($schoolTagline) && trim($schoolTagline) !== '') {
            $trimmedTag = trim($schoolTagline);
            $newSettings['school_tagline'] = $trimmedTag;
            $newSettings['site_tagline'] = $trimmedTag;
            try {
                if (class_exists(\Modules\Core\System\Models\Setting::class)) {
                    \Modules\Core\System\Models\Setting::set('site_tagline', $trimmedTag, 'string', 'general');
                    \Modules\Core\System\Models\Setting::clearCache('site_tagline');
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to sync school_tagline to site_tagline: '.$e->getMessage());
            }
        }
    }
}
