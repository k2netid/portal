<?php

declare(strict_types=1);

namespace Modules\Layout\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Layout\Models\Theme;
use ZipArchive;

final class ThemePackageInstallService
{
    public function __construct(
        private readonly ThemeService $themeService,
    ) {}

    public function isEnabled(): bool
    {
        if (! config('layout.uploaded_themes.enabled', true)) {
            return false;
        }

        if (class_exists(\Modules\Core\System\Models\Setting::class)) {
            $settingAllowed = filter_var(\Modules\Core\System\Models\Setting::get('enable_theme_upload', true), FILTER_VALIDATE_BOOLEAN);
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
            $licenseService = app(\Modules\Core\System\Services\LicenseService::class);
            if (! $licenseService->canUseFeature('theme_upload')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{theme: Theme, warnings: list<string>}
     */
    public function installFromZip(UploadedFile $file): array
    {
        if (! $this->isEnabled()) {
            throw new \RuntimeException('Uploaded themes are disabled. Set FEATURE_UPLOADED_THEMES=true.');
        }

        $maxBytes = config('layout.uploaded_themes.max_zip_bytes', 52_428_800);
        if ($file->getSize() > $maxBytes) {
            throw new \InvalidArgumentException('Theme package exceeds maximum upload size.');
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext !== 'zip') {
            throw new \InvalidArgumentException('Only .zip theme packages are supported.');
        }

        $tempDir = storage_path('app/temp/theme-upload-'.Str::random(16));
        File::ensureDirectoryExists($tempDir);

        $zipPath = $tempDir.'/package.zip';
        $file->move($tempDir, 'package.zip');

        $extractDir = $tempDir.'/extracted';
        File::ensureDirectoryExists($extractDir);

        $warnings = [];

        try {
            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                throw new \InvalidArgumentException('Invalid or corrupted ZIP archive.');
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);
                if (str_contains($name, '..') || str_starts_with($name, '/')) {
                    throw new \InvalidArgumentException('ZIP contains unsafe paths.');
                }
            }

            $zip->extractTo($extractDir);
            $zip->close();

            $packageRoot = $this->resolvePackageRoot($extractDir);
            $manifestPath = $packageRoot.'/theme.json';
            if (! is_file($manifestPath)) {
                throw new \InvalidArgumentException('theme.json is required at package root.');
            }

            /** @var array<string, mixed>|null $manifest */
            $manifest = json_decode((string) file_get_contents($manifestPath), true);
            if (! is_array($manifest)) {
                throw new \InvalidArgumentException('theme.json must be valid JSON.');
            }

            $slugRaw = $manifest['slug'] ?? basename($packageRoot);
            $slug = Str::slug(is_scalar($slugRaw) ? (string) $slugRaw : '');
            if ($slug === '') {
                throw new \InvalidArgumentException('theme.json must define a valid slug.');
            }

            if ($this->verifyBundleChecksum($packageRoot, $manifest, $warnings) === false) {
                throw new \InvalidArgumentException('theme.esm.js checksum mismatch (bundle_checksum).');
            }

            $dest = storage_path('app/public/themes/'.$slug);
            if (is_dir($dest)) {
                File::deleteDirectory($dest);
            }
            File::ensureDirectoryExists(dirname($dest));
            File::copyDirectory($packageRoot, $dest);

            $this->themeService->scanThemes();

            $theme = Theme::query()->where('slug', $slug)->first();
            if (! $theme instanceof Theme) {
                throw new \RuntimeException("Theme {$slug} was extracted but not registered.");
            }

            $errors = $theme->validate();
            if ($errors !== []) {
                $warnings = array_merge($warnings, $errors);
            }

            return ['theme' => $theme->fresh() ?? $theme, 'warnings' => $warnings];
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    private function resolvePackageRoot(string $extractDir): string
    {
        if (is_file($extractDir.'/theme.json')) {
            return $extractDir;
        }

        $children = array_filter(scandir($extractDir) ?: [], static fn (string $f): bool => ! in_array($f, ['.', '..'], true));
        if (count($children) === 1) {
            $only = $extractDir.DIRECTORY_SEPARATOR.reset($children);
            if (is_dir($only) && is_file($only.'/theme.json')) {
                return $only;
            }
        }

        throw new \InvalidArgumentException('ZIP must contain theme.json at root or single top-level folder.');
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @param  list<string>  $warnings
     */
    private function verifyBundleChecksum(string $packageRoot, array $manifest, array &$warnings): bool
    {
        $expected = $manifest['bundle_checksum'] ?? null;
        if (! is_string($expected) || trim($expected) === '') {
            return true;
        }

        $bundlePath = $packageRoot.'/theme.esm.js';
        if (! is_file($bundlePath)) {
            $warnings[] = 'bundle_checksum set but theme.esm.js is missing';

            return true;
        }

        $hash = hash_file('sha256', $bundlePath);
        if (! is_string($hash) || ! hash_equals(strtolower(trim($expected)), strtolower($hash))) {
            return false;
        }

        return true;
    }
}
