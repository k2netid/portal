<?php

namespace Modules\Core\System\Tests\Unit;

use Illuminate\Support\Facades\File;
use Modules\Core\System\Support\ExtensionPaths;
use Tests\TestCase;

class ExtensionPathsTest extends TestCase
{
    public function test_plugin_directory_resolves_under_extensions_root(): void
    {
        $slug = 'ext-path-test-'.uniqid();
        $expected = ExtensionPaths::root().DIRECTORY_SEPARATOR.$slug;

        $this->assertSame($expected, ExtensionPaths::pluginDirectory($slug));
    }

    public function test_discover_only_lists_packages_under_extensions_root(): void
    {
        $slug = 'discover-test-'.uniqid();
        $packageDir = ExtensionPaths::pluginDirectory($slug);
        File::ensureDirectoryExists($packageDir);
        File::put($packageDir.'/manifest.json', '{"slug":"'.$slug.'"}');

        try {
            $paths = ExtensionPaths::discoverPluginPackageDirectories();
            $this->assertContains($packageDir, $paths);
            foreach ($paths as $path) {
                $this->assertStringStartsWith(ExtensionPaths::root(), $path);
            }
        } finally {
            File::deleteDirectory($packageDir);
        }
    }
}
