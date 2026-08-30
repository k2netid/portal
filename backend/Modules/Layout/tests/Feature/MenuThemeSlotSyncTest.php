<?php

declare(strict_types=1);

namespace Modules\Layout\Tests\Feature;

use Modules\Layout\Models\Menu;
use Modules\Layout\Models\Theme;
use Modules\Layout\Services\ThemeService;
use Tests\TestCase;

class MenuThemeSlotSyncTest extends TestCase
{
    public function test_updating_menu_location_writes_theme_menu_location_setting(): void
    {
        $this->activatePack('layout');
        $this->seedPermissionsAndRoles();
        Theme::query()->delete();

        $theme = Theme::factory()->create([
            'name' => 'Janari',
            'slug' => 'janari',
            'type' => 'frontend',
            'status' => 'active',
            'is_active' => true,
            'settings' => [],
        ]);

        $menu = Menu::query()->create([
            'name' => 'Header Nav',
            'slug' => 'header-nav-'.uniqid(),
            'location' => null,
            'is_active' => true,
        ]);

        $user = $this->createSuperAdminUser();
        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/manage/layout/menus/'.$menu->id, [
                'location' => 'header',
            ])
            ->assertOk();

        $theme->refresh();
        $this->assertSame((string) $menu->id, $theme->settings['menu_location_header'] ?? null);
    }

    public function test_public_menu_show_by_uuid(): void
    {
        $this->activatePack('layout');

        $menu = Menu::query()->create([
            'name' => 'Public Nav',
            'slug' => 'public-nav-'.uniqid(),
            'location' => 'header',
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/public/layout/menus/'.$menu->id)
            ->assertOk()
            ->assertJsonPath('data.id', $menu->id);
    }

    public function test_customization_publish_keeps_menu_location_keys(): void
    {
        $theme = Theme::factory()->create([
            'name' => 'Janari Publish',
            'slug' => 'janari-publish-'.uniqid(),
            'type' => 'frontend',
            'status' => 'active',
            'is_active' => false,
            'settings' => [
                'menu_location_header' => 'keep-me',
            ],
        ]);

        $published = app(ThemeService::class)->settingsForCustomizationPublish($theme, [
            'site_title' => 'X',
            'menu_location_footer' => 'footer-id',
        ]);

        $this->assertSame('keep-me', $published['menu_location_header'] ?? null);
        $this->assertSame('footer-id', $published['menu_location_footer'] ?? null);
    }
}
