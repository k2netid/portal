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

        $storageThemes = 'storage/app/themes';
        if (is_dir(base_path($storageThemes))) {
            return $storageThemes;
        }

        $monorepoCandidates = [
            '../frontend/src/modules/Content/Layout/views/themes',
            '../frontend/src/modules/Content/Publishing/views/themes',
            '../frontend/src/modules/Jejakawan/views/themes',
            '../frontend/src/modules/Publishing/views/themes',
        ];

        if (app()->environment('local', 'development', 'testing')) {
            foreach ($monorepoCandidates as $candidate) {
                if (is_dir(base_path($candidate))) {
                    return $candidate;
                }
            }
        }

        return '../frontend/src/modules/Content/Layout/views/themes';
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

        $configured = self::relativePathFromBackendRoot();
        $absConfigured = base_path($configured);
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
        return base_path(self::relativePathFromBackendRoot());
    }

    public static function pathForSlug(string $slug): string
    {
        return self::rootPath().DIRECTORY_SEPARATOR.$slug;
    }

    /**
     * @return array{relative: string, absolute: string, exists: bool, environment: string}
     */
    public static function diagnostics(): array
    {
        $relative = self::relativePathFromBackendRoot();
        $absolute = base_path($relative);

        return [
            'relative' => $relative,
            'absolute' => $absolute,
            'exists' => is_dir($absolute),
            'environment' => (string) app()->environment(),
        ];
    }
}
