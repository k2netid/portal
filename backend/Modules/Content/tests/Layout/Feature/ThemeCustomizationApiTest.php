<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Layout\Services\ThemeCacheService;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class ThemeCustomizationApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();
    }

    public function test_customization_publish_replaces_stale_settings_keys(): void
    {
        $theme = Theme::factory()->create([
            'slug' => 'janari',
            'path' => 'janari',
            'type' => 'frontend',
            'is_active' => false,
            'settings' => [
                'color_primary' => '#111111',
                'obsolete_key' => 'should_be_removed',
                'theme_data_bindings' => [],
            ],
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/manage/layout/themes/{$theme->slug}/customization", [
                'settings' => [
                    'color_primary' => '#222222',
                    'theme_data_bindings' => [],
                ],
                'custom_css' => '.hero { color: red; }',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.settings.color_primary', '#222222');

        $theme->refresh();
        $this->assertSame('#222222', $theme->settings['color_primary'] ?? null);
        $this->assertArrayNotHasKey('obsolete_key', $theme->settings ?? []);
        $this->assertSame('.hero { color: red; }', $theme->custom_css);
    }

    public function test_customization_publish_clears_theme_cache(): void
    {
        $theme = Theme::factory()->create([
            'slug' => 'cache-theme',
            'type' => 'frontend',
            'is_active' => true,
            'settings' => ['color_primary' => '#000000'],
        ]);

        Cache::put(
            ThemeCacheService::PREFIX_ACTIVE_API_PAYLOAD.'frontend',
            ['settings' => ['color_primary' => '#000000']],
            3600,
        );

        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/manage/layout/themes/{$theme->slug}/customization", [
                'settings' => [
                    'color_primary' => '#abcdef',
                    'theme_data_bindings' => [],
                ],
                'custom_css' => null,
            ])
            ->assertStatus(200);

        $this->assertFalse(
            Cache::has(ThemeCacheService::PREFIX_ACTIVE_API_PAYLOAD.'frontend'),
        );
    }
}
