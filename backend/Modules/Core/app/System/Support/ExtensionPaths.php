<?php

declare(strict_types=1);

namespace Modules\Core\System\Support;

/**
 * Resolve dynamic plugin paths under backend/extensions/{slug}.
 */
final class ExtensionPaths
{
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
        $root = self::root();
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
