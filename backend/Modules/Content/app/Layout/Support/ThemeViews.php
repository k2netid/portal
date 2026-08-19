<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Support;

/**
 * Filesystem root for Vue themes (code-first; may include runtime storage in Fase 3).
 *
 * Override with PUBLISHING_THEME_VIEWS_RELATIVE_PATH when backend is not in the default monorepo layout.
 */
final class ThemeViews
{
    public static function relativePathFromBackendRoot(): string
    {
        $raw = config('publishing.theme_views_relative_path');
        if (is_string($raw) && trim($raw) !== '') {
            $configured = trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $raw), DIRECTORY_SEPARATOR);
            if (is_dir(base_path($configured))) {
                return $configured;
            }
        }

        // 1. Check resources/themes (bundled directly with backend)
        $resourcesThemes = 'resources/themes';
        if (is_dir(base_path($resourcesThemes))) {
            return $resourcesThemes;
        }

        // 2. Check storage/app/themes
        $storageThemes = 'storage/app/themes';
        if (is_dir(base_path($storageThemes))) {
            return $storageThemes;
        }

        // 3. Monorepo development relative candidates
        $monorepoCandidates = [
            '../frontend/src/modules/Content/Layout/views/themes',
            '../frontend/src/modules/Content/Publishing/views/themes',
            '../frontend/src/modules/Jejakawan/views/themes',
            '../frontend/src/modules/Publishing/views/themes',
        ];

        foreach ($monorepoCandidates as $candidate) {
            if (is_dir(base_path($candidate))) {
                return $candidate;
            }
        }

        // 4. Absolute dev workspace path fallback if on same host
        $devAbsolute = '/home/jejakawan/dev/ja-cms/frontend/src/modules/Content/Layout/views/themes';
        if (is_dir($devAbsolute)) {
            return $devAbsolute;
        }

        return 'resources/themes';
    }

    /**
     * All filesystem roots to scan for theme packages (bundled monorepo + uploaded storage).
     *
     * @return list<array{path: string, source: string}>
     */
    public static function scanRootPaths(): array
    {
        $roots = [];
        $seen = [];

        $absConfigured = self::rootPath();
        if (is_dir($absConfigured)) {
            $roots[] = ['path' => $absConfigured, 'source' => 'bundled'];
            $seen[$absConfigured] = true;
        }

        $uploaded = storage_path('app/public/themes');
        if (is_dir($uploaded) && ! isset($seen[$uploaded])) {
            $roots[] = ['path' => $uploaded, 'source' => 'uploaded'];
        }

        return $roots;
    }

    public static function rootPath(): string
    {
        $relative = self::relativePathFromBackendRoot();
        if (str_starts_with($relative, '/') || (strlen($relative) > 2 && $relative[1] === ':')) {
            return $relative;
        }

        return base_path($relative);
    }

    public static function pathForSlug(string $slug): string
    {
        // 1. Check under configured root path
        $rootCandidate = self::rootPath().DIRECTORY_SEPARATOR.$slug;
        if (is_dir($rootCandidate)) {
            return $rootCandidate;
        }

        // 2. Check resources/themes
        $resourcesCandidate = base_path('resources/themes'.DIRECTORY_SEPARATOR.$slug);
        if (is_dir($resourcesCandidate)) {
            return $resourcesCandidate;
        }

        // 3. Check storage/app/themes
        $storageCandidate = base_path('storage/app/themes'.DIRECTORY_SEPARATOR.$slug);
        if (is_dir($storageCandidate)) {
            return $storageCandidate;
        }

        // 4. Check dev workspace absolute path
        $devCandidate = '/home/jejakawan/dev/ja-cms/frontend/src/modules/Content/Layout/views/themes'.DIRECTORY_SEPARATOR.$slug;
        if (is_dir($devCandidate)) {
            return $devCandidate;
        }

        return $rootCandidate;
    }

    /**
     * @return array{relative: string, absolute: string, exists: bool, environment: string}
     */
    public static function diagnostics(): array
    {
        $relative = self::relativePathFromBackendRoot();
        $absolute = self::rootPath();

        return [
            'relative' => $relative,
            'absolute' => $absolute,
            'exists' => is_dir($absolute),
            'environment' => (string) app()->environment(),
        ];
    }
}
