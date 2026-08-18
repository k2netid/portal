<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Feature;

use Illuminate\Support\Facades\File;
use Modules\Content\Layout\Support\ThemeViews;
use Tests\TestCase;

class ThemePackageCommandTest extends TestCase
{
    public function test_theme_package_copies_bundled_janari_to_storage(): void
    {
        $source = ThemeViews::pathForSlug('janari');
        $this->assertDirectoryExists($source);
        $this->assertFileExists($source.'/theme.json');

        $dest = storage_path('framework/testing/theme-package-'.uniqid('', true));

        $this->artisan('theme:package', ['slug' => 'janari', '--output' => $dest])
            ->assertSuccessful();

        $this->assertFileExists($dest.'/theme.json');
        $this->assertFileExists($dest.'/pages/Home.vue');

        File::deleteDirectory($dest);
    }
}
