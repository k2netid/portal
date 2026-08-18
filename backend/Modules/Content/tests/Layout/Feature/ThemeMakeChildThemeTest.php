<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Layout\Services\ThemeService;
use Modules\Content\Layout\Support\ThemeViews;
use Tests\TestCase;

class ThemeMakeChildThemeTest extends TestCase
{
    use RefreshDatabase;

    private string $parentSlug = 'test-parent-theme-reg';

    private string $childSlug = 'test-child-theme-reg';

    protected function tearDown(): void
    {
        foreach ([$this->childSlug, $this->parentSlug] as $slug) {
            Theme::query()->where('slug', $slug)->delete();
            $dir = ThemeViews::rootPath().'/'.$slug;
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }

        parent::tearDown();
    }

    public function test_theme_make_with_parent_registers_child_and_inherits_parent_menu_locations(): void
    {
        $parent = Theme::factory()->create([
            'slug' => $this->parentSlug,
            'path' => $this->parentSlug,
            'type' => 'frontend',
            'status' => 'active',
        ]);

        $parentPath = $parent->getThemePath();
        File::ensureDirectoryExists($parentPath);
        File::put($parentPath.'/theme.json', json_encode([
            'menus' => ['primary' => 'Primary Menu'],
        ], JSON_THROW_ON_ERROR));

        $exit = Artisan::call('theme:make', [
            'name' => 'Regression Child',
            '--slug' => $this->childSlug,
            '--parent' => $this->parentSlug,
            '--type' => 'frontend',
            '--no-interaction' => true,
        ]);

        $this->assertSame(0, $exit);

        $child = Theme::query()->where('slug', $this->childSlug)->first();
        $this->assertNotNull($child);
        $this->assertSame($this->parentSlug, $child->parent_theme);

        $manifest = json_decode((string) file_get_contents($child->getThemePath().'/theme.json'), true);
        $this->assertIsArray($manifest);
        $this->assertSame($this->parentSlug, $manifest['parent_theme'] ?? null);

        File::put($child->getThemePath().'/theme.json', json_encode([
            'parent_theme' => $this->parentSlug,
        ], JSON_THROW_ON_ERROR));

        $locations = (new ThemeService)->getMenuLocations($child);
        $this->assertSame(['primary'], $locations);
    }
}
