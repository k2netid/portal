<?php

declare(strict_types=1);

namespace Modules\Core\System\Support;

/**
 * Resolve dynamic extension paths.
 *
 * Covers:
 *  - backend/extensions/{slug}  → uploaded plugins
 *  - backend/theme-packs/{slug} → first-party theme packs (ADR-023)
 */
final class ExtensionPaths
{
    // -----------------------------------------------------------------------
    // Plugin paths (uploaded plugins under backend/extensions/)
    // -----------------------------------------------------------------------

    public static function root(): string
    {
        $root = base_path('extensions');
        if (! is_dir($root)) {
            @mkdir($root, 0755, true);
        }

        return $root;
    }

    public static function pluginDirectory(string $slug): string
    {
        return self::root().DIRECTORY_SEPARATOR.trim($slug, '/');
    }

    public static function pluginSrcDirectory(string $slug): string
    {
        return self::pluginDirectory($slug).'/src';
    }

    public static function pluginManifestPath(string $slug): string
    {
        return self::pluginDirectory($slug).'/manifest.json';
    }

    public static function pluginMigrationsDirectory(string $slug): string
    {
        return self::pluginDirectory($slug).'/database/migrations';
    }

    /**
     * @return array<int, string> Absolute paths to plugin package directories.
     */
    public static function discoverPluginPackageDirectories(): array
    {
        return self::discoverDirectoriesUnder(self::root());
    }

    // -----------------------------------------------------------------------
    // Theme-pack paths (first-party thin packs under backend/theme-packs/)
    // ADR-023: theme packs are manifests that point to views/themes/<slug>/.
    // -----------------------------------------------------------------------

    public static function themePacksRoot(): string
    {
        $root = base_path('theme-packs');
        if (! is_dir($root)) {
            @mkdir($root, 0755, true);
        }

        return $root;
    }

    public static function themePackDirectory(string $packSlug): string
    {
        return self::themePacksRoot().DIRECTORY_SEPARATOR.trim($packSlug, '/');
    }

    public static function themePackManifestPath(string $packSlug): string
    {
        return self::themePackDirectory($packSlug).'/manifest.json';
    }

    /**
     * @return array<int, string> Absolute paths to theme-pack directories.
     */
    public static function discoverThemePackDirectories(): array
    {
        return self::discoverDirectoriesUnder(self::themePacksRoot());
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * @return array<int, string>
     */
    private static function discoverDirectoriesUnder(string $root): array
    {
        $directories = [];
        $entries = scandir($root);

        if ($entries === false) {
            return [];
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $root.DIRECTORY_SEPARATOR.$entry;
            if (is_dir($path)) {
                $directories[] = $path;
            }
        }

        return $directories;
    }
}
