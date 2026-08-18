<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Modules\Content\Layout\Models\Theme;
use Tests\TestCase;

class ThemeStagingUploadedCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_staging_uploaded_sets_bundle_url_and_revert_restores_bundled(): void
    {
        config(['content.layout.uploaded_themes_enabled' => true]);

        $dir = storage_path('app/public/themes/janari-test');
        File::ensureDirectoryExists($dir);
        File::put($dir.'/theme.esm.js', "export function resolveComponent() { return null; }\n");
        File::put($dir.'/theme.json', json_encode(['bundle_url' => '/storage/themes/janari-test/theme.esm.js'], JSON_THROW_ON_ERROR));

        $theme = Theme::factory()->create([
            'slug' => 'janari-test',
            'type' => 'frontend',
            'source' => 'bundled',
            'is_active' => true,
        ]);

        $this->artisan('theme:staging-uploaded', ['slug' => 'janari-test'])
            ->assertSuccessful();

        $theme->refresh();
        $this->assertSame('uploaded', $theme->source);
        $this->assertSame('/storage/themes/janari-test/theme.esm.js', $theme->bundle_url);

        $this->artisan('theme:staging-uploaded', ['slug' => 'janari-test', '--revert' => true])
            ->assertSuccessful();

        $theme->refresh();
        $this->assertSame('bundled', $theme->source);
        $this->assertNull($theme->bundle_url);

        File::deleteDirectory($dir);
    }
}
