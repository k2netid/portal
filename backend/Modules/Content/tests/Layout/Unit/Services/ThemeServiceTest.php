<?php

namespace Modules\Content\Layout\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Layout\Services\ThemeHooksService;
use Modules\Content\Layout\Services\ThemeService;
use Modules\Content\Layout\Support\ThemeViews;
use Modules\Core\System\Models\Plugin;
use Tests\TestCase;

class ThemeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ThemeService $service;

    protected string $tempThemesDir;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->service = new ThemeService;

        // Setup temp theme directory for scanning tests
        $this->tempThemesDir = base_path('resources/js/views/themes_test_root');
        if (! File::isDirectory($this->tempThemesDir)) {
            File::makeDirectory($this->tempThemesDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->tempThemesDir)) {
            File::deleteDirectory($this->tempThemesDir);
        }

        // Clean up any themes created in real themes dir by tests
        $realThemesDir = ThemeViews::rootPath();

        $testThemes = array_merge(
            glob($realThemesDir.'/test-*') ?: [],
            glob($realThemesDir.'/c-assets') ?: [],
            glob($realThemesDir.'/p-assets') ?: [],
            glob($realThemesDir.'/critical') ?: []
        );

        foreach ($testThemes as $dir) {
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }

        parent::tearDown();
    }

    public function test_get_active_theme(): void
    {
        $theme = Theme::factory()->create(['is_active' => true, 'type' => 'frontend', 'status' => 'active', 'slug' => 'test-active']);
        $result = $this->service->getActiveTheme('frontend');
        $this->assertEquals($theme->id, $result->id);
    }

    public function test_get_menu_locations(): void
    {
        $theme = Theme::factory()->create([
            'slug' => 'test-menu',
            'path' => 'test-menu',
            'settings' => [],
        ]);

        $path = $theme->getThemePath();
        File::ensureDirectoryExists($path);
        File::put($path.'/theme.json', json_encode(['menus' => ['primary' => 'Primary Menu']]));

        $locations = $this->service->getMenuLocations($theme);
        $this->assertEquals(['primary'], $locations);
    }

    public function test_get_menu_locations_parent_fallback(): void
    {
        $parent = Theme::factory()->create(['slug' => 'test-parent']);
        $child = Theme::factory()->create(['slug' => 'test-child', 'parent_theme' => 'test-parent']);

        $parentPath = $parent->getThemePath();
        File::ensureDirectoryExists($parentPath);
        File::put($parentPath.'/theme.json', json_encode(['menus' => ['main' => 'Main']]));

        File::ensureDirectoryExists($child->getThemePath());
        File::put($child->getThemePath().'/theme.json', json_encode([]));

        $locations = $this->service->getMenuLocations($child);
        $this->assertEquals(['main'], $locations);
    }

    public function test_activate_and_deactivate_theme(): void
    {
        $theme = Theme::factory()->create(['slug' => 'test-act', 'is_active' => false]);

        $hooks = $this->createMock(ThemeHooksService::class);
        $hooks->expects($this->exactly(2))->method('doAction');

        $service = new ThemeService($hooks);
        $service->activateTheme($theme);

        $this->assertTrue($theme->fresh()->is_active);

        $service->deactivateTheme($theme);
        $this->assertFalse($theme->fresh()->is_active);
    }

    public function test_activate_theme_with_validation_errors(): void
    {
        $theme = Theme::factory()->create(['slug' => 'test-invalid']);
        $this->service->activateTheme($theme);
        $this->assertTrue($theme->fresh()->is_active);
    }

    public function test_get_theme_setting_with_inheritance(): void
    {
        Theme::factory()->create(['slug' => 'test-set-p', 'settings' => ['color' => 'red']]);
        $child = Theme::factory()->create(['slug' => 'test-set-c', 'parent_theme' => 'test-set-p', 'settings' => []]);

        $this->assertEquals('red', $this->service->getThemeSetting($child, 'color'));

        $child->setSetting('color', 'blue');
        $this->assertEquals('blue', $this->service->getThemeSetting($child, 'color'));
    }

    public function test_get_theme_setting_manifest_fallback(): void
    {
        $theme = Theme::factory()->create(['slug' => 'test-manifest', 'settings' => []]);
        $path = $theme->getThemePath();
        File::ensureDirectoryExists($path);
        File::put($path.'/theme.json', json_encode([
            'settings_schema' => [
                'font_size' => ['default' => '16px'],
            ],
        ]));

        $this->assertEquals('16px', $this->service->getThemeSetting($theme, 'font_size'));
    }

    public function test_load_theme_assets(): void
    {
        $theme = Theme::factory()->create(['slug' => 'test-assets', 'path' => 'test-assets']);
        $path = $theme->getThemePath();
        File::ensureDirectoryExists($path.'/assets/css');
        File::ensureDirectoryExists($path.'/assets/js');
        File::put($path.'/assets/css/style.css', '');
        File::put($path.'/assets/js/app.js', '');

        $assets = $this->service->loadThemeAssets($theme);
        $this->assertContains('themes/test-assets/assets/css/style.css', $assets['css']);
        $this->assertContains('themes/test-assets/assets/js/app.js', $assets['js']);
    }

    public function test_load_theme_assets_from_manifest(): void
    {
        $theme = Theme::factory()->create(['slug' => 'test-man-assets', 'path' => 'test-man-assets']);
        $path = $theme->getThemePath();
        File::ensureDirectoryExists($path);
        File::put($path.'/theme.json', json_encode([
            'assets' => [
                'css' => ['custom.css'],
                'js' => ['custom.js'],
            ],
        ]));

        $assets = $this->service->loadThemeAssets($theme);
        $this->assertContains('themes/test-man-assets/custom.css', $assets['css']);
    }

    public function test_check_dependencies(): void
    {
        $theme = Theme::factory()->create(['slug' => 'test-dep', 'dependencies' => []]);
        $this->assertTrue($this->service->checkDependencies($theme));

        $theme->update(['dependencies' => ['themes' => ['missing-parent']]]);
        $this->assertFalse($this->service->checkDependencies($theme));

        Theme::factory()->create(['slug' => 'parent-active', 'is_active' => true]);
        $theme->update(['dependencies' => ['themes' => ['parent-active']]]);
        $this->assertTrue($this->service->checkDependencies($theme));
    }

    public function test_get_theme_custom_css(): void
    {
        Theme::factory()->create(['slug' => 'test-css-p', 'custom_css' => 'body { color: red; }']);
        $child = Theme::factory()->create(['slug' => 'test-css-c', 'parent_theme' => 'test-css-p', 'custom_css' => 'h1 { font-size: 2em; }']);

        $css = $this->service->getThemeCustomCss($child);
        $this->assertStringContainsString('red', $css);
        $this->assertStringContainsString('2em', $css);
    }

    public function test_get_theme_css_variables(): void
    {
        $theme = Theme::factory()->create(['slug' => 'test-css-var', 'settings' => ['primary_color' => '#fff']]);
        $path = $theme->getThemePath();
        File::ensureDirectoryExists($path);
        File::put($path.'/theme.json', json_encode([
            'settings_schema' => [
                'primary_color' => ['type' => 'color'],
            ],
        ]));

        $css = $this->service->getThemeCssVariables($theme);
        $this->assertStringContainsString('--theme-primary-color: #fff', $css);
    }

    public function test_scan_themes(): void
    {
        $service = new class extends ThemeService
        {
            public function getThemeDirectory(): string
            {
                return base_path('resources/js/views/themes_test_root');
            }
        };

        $themeDir = base_path('resources/js/views/themes_test_root/new-theme');
        File::ensureDirectoryExists($themeDir);
        File::put($themeDir.'/theme.json', json_encode([
            'name' => 'New Theme',
            'version' => '1.1.0',
        ]));

        $themes = $service->scanThemes();
        $this->assertCount(1, $themes);
        $this->assertEquals('new-theme', $themes[0]->slug);
    }

    public function test_ensure_theme_directory(): void
    {
        $this->assertTrue($this->service->ensureThemeDirectory());
    }

    public function test_activate_theme_critical_error(): void
    {
        $theme = Theme::factory()->create(['slug' => 'critical']);
        $path = $theme->getThemePath();
        File::ensureDirectoryExists($path);
        // Invalid JSON to trigger critical error
        File::put($path.'/theme.json', '{invalid}');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Theme validation failed');
        $this->service->activateTheme($theme);
    }

    public function test_check_dependencies_with_plugins(): void
    {
        $theme = Theme::factory()->create(['slug' => 'test-plug', 'dependencies' => ['plugins' => ['missing-plugin']]]);
        $this->assertFalse($this->service->checkDependencies($theme));

        Plugin::create([
            'slug' => 'active-plug',
            'name' => 'Active',
            'main_file' => 'plugin.php',
            'is_active' => true,
        ]);
        $theme->update(['dependencies' => ['plugins' => ['active-plug']]]);
        $this->assertTrue($this->service->checkDependencies($theme));
    }

    public function test_load_theme_assets_with_parent_merge(): void
    {
        $parent = Theme::factory()->create(['slug' => 'p-assets', 'path' => 'p-assets']);
        $child = Theme::factory()->create(['slug' => 'c-assets', 'path' => 'c-assets', 'parent_theme' => 'p-assets']);

        $pPath = $parent->getThemePath();
        File::ensureDirectoryExists($pPath.'/assets/css');
        File::put($pPath.'/assets/css/parent.css', '');

        $cPath = $child->getThemePath();
        File::ensureDirectoryExists($cPath.'/assets/css');
        File::put($cPath.'/assets/css/child.css', '');

        $assets = $this->service->loadThemeAssets($child);
        $this->assertContains('themes/p-assets/assets/css/parent.css', $assets['css']);
        $this->assertContains('themes/c-assets/assets/css/child.css', $assets['css']);
    }

    public function test_clear_theme_cache_all(): void
    {
        $this->service->clearThemeCache();
        $this->assertTrue(true); // No error means success
    }

    public function test_get_default_settings_schema(): void
    {
        $schema = $this->service->getDefaultSettingsSchema();
        $this->assertArrayHasKey('primary_color', $schema);
    }
}
