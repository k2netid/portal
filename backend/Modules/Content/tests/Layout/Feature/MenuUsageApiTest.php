<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Layout\Models\Menu;
use Modules\Content\Layout\Models\Theme;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class MenuUsageApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();
    }

    public function test_usage_reports_theme_assignment_and_public_location(): void
    {
        $menu = Menu::create([
            'name' => 'Header Primary',
            'slug' => 'header-primary',
            'location' => 'header',
            'is_active' => true,
        ]);
        $menu->items()->create(['title' => 'Home', 'url' => '/', 'type' => 'link']);

        $theme = Theme::factory()->create([
            'slug' => 'janari',
            'path' => 'janari',
            'type' => 'frontend',
            'is_active' => true,
            'settings' => [
                'menu_location_header' => $menu->id,
            ],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/layout/menus/{$menu->id}/usage");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_in_use', true)
            ->assertJsonPath('data.is_served_on_public', true);

        $types = collect($response->json('data.usages'))->pluck('type')->all();
        $this->assertContains('theme_assignment', $types);
        $this->assertContains('public_location', $types);
    }

    public function test_force_delete_blocked_when_menu_in_use(): void
    {
        $menu = Menu::create([
            'name' => 'Footer',
            'slug' => 'footer-menu',
            'location' => 'footer',
            'is_active' => true,
        ]);

        Theme::factory()->create([
            'slug' => 'janari',
            'path' => 'janari',
            'type' => 'frontend',
            'is_active' => true,
            'settings' => ['menu_location_footer' => $menu->id],
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/manage/layout/menus/{$menu->id}/force-delete")
            ->assertStatus(422);

        $this->assertDatabaseHas('lay_menus', ['id' => $menu->id]);
    }

    public function test_soft_delete_allowed_when_in_use(): void
    {
        $menu = Menu::create([
            'name' => 'Nav',
            'slug' => 'nav-menu',
            'location' => 'header_top',
            'is_active' => true,
        ]);

        Theme::factory()->create([
            'type' => 'frontend',
            'is_active' => true,
            'settings' => ['menu_location_header_top' => $menu->id],
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/manage/layout/menus/{$menu->id}")
            ->assertStatus(200);

        $this->assertSoftDeleted('lay_menus', ['id' => $menu->id]);
    }
}
