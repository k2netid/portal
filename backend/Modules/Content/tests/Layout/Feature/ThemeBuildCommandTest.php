<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ThemeBuildCommandTest extends TestCase
{
    public function test_theme_build_writes_bundle_checksum_to_manifest(): void
    {
        $dir = storage_path('framework/testing/theme-build-'.uniqid('', true));
        File::ensureDirectoryExists($dir);
        File::put($dir.'/theme.esm.js', "export function resolveComponent() { return null; }\n");
        File::put($dir.'/theme.json', json_encode(['name' => 'Test'], JSON_THROW_ON_ERROR));

        $this->artisan('theme:build', ['slug' => 'test-theme', '--output' => $dir])
            ->assertSuccessful();

        $manifest = json_decode((string) File::get($dir.'/theme.json'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertNotEmpty($manifest['bundle_checksum'] ?? null);
        $this->assertSame(64, strlen((string) $manifest['bundle_checksum']));

        File::deleteDirectory($dir);
    }
}
