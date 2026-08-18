<?php

namespace Modules\Content\Layout\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Layout\Models\Menu;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();
    }

    public function test_can_list_menus(): void
    {
        Menu::create(['name' => 'Main Menu', 'slug' => 'main', 'location' => 'header']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/layout/menus');

        $response->assertStatus(200)
            ->assertJsonPath('data.data.0.name', 'Main Menu');
    }

    public function test_list_trashed_only_excludes_active_menus(): void
    {
        $active = Menu::create(['name' => 'Active Menu', 'slug' => 'active-menu', 'location' => 'header']);
        $trashed = Menu::create(['name' => 'Trashed Menu', 'slug' => 'trashed-menu', 'location' => 'footer']);
        $trashed->delete();

        $activeOnly = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/layout/menus?trashed=without');

        $activeOnly->assertStatus(200)
            ->assertJsonPath('meta.trashed_count', 1)
            ->assertJsonFragment(['name' => 'Active Menu'])
            ->assertJsonMissing(['name' => 'Trashed Menu']);

        $trashedOnly = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/layout/menus?trashed=only');

        $trashedOnly->assertStatus(200)
            ->assertJsonFragment(['name' => 'Trashed Menu'])
            ->assertJsonMissing(['name' => 'Active Menu']);
    }

    public function test_search_with_trashed_only_does_not_error(): void
    {
        $trashed = Menu::create(['name' => 'Sidebar Old', 'slug' => 'sidebar-old', 'location' => 'sidebar']);
        $trashed->delete();

        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/layout/menus?trashed=only&search=side')
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Sidebar Old']);
    }

    public function test_can_create_menu(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/layout/menus', [
                'name' => 'Footer Menu',
                'location' => 'footer',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('lay_menus', ['name' => 'Footer Menu']);
    }

    public function test_can_add_menu_item(): void
    {
        $menu = Menu::create(['name' => 'Main Menu', 'slug' => 'main', 'location' => 'header']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/layout/menus/{$menu->id}/items", [
                'title' => 'Home',
                'url' => '/',
                'type' => 'link',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('lay_menu_items', [
            'menu_id' => $menu->id,
            'title' => 'Home',
        ]);
    }

    public function test_can_list_menu_items(): void
    {
        $menu = Menu::create(['name' => 'Main Menu', 'slug' => 'main-list', 'location' => 'sidebar']);
        $menu->items()->create(['title' => 'About', 'url' => '/about', 'type' => 'link']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/layout/menus/{$menu->id}/items");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_update_and_delete_menu_item(): void
    {
        $menu = Menu::create(['name' => 'Nav', 'slug' => 'nav', 'location' => 'header_top']);
        $item = $menu->items()->create(['title' => 'Old', 'url' => '/old', 'type' => 'link']);

        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/manage/layout/menus/{$menu->id}/items/{$item->id}", [
                'title' => 'New',
                'url' => '/new',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'New');

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/manage/layout/menus/{$menu->id}/items/{$item->id}")
            ->assertStatus(200);

        $this->assertSoftDeleted('lay_menu_items', ['id' => $item->id]);
    }

    public function test_rejects_dangerous_menu_item_url(): void
    {
        $menu = Menu::create(['name' => 'XSS', 'slug' => 'xss', 'location' => 'footer_col_1']);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/layout/menus/{$menu->id}/items", [
                'title' => 'Bad',
                'url' => 'javascript:alert(1)',
                'type' => 'link',
            ])
            ->assertStatus(422);
    }

    public function test_reorder_purges_orphaned_items(): void
    {
        $menu = Menu::create(['name' => 'Reorder', 'slug' => 'reorder', 'location' => 'footer_col_2']);
        $keep = $menu->items()->create(['title' => 'Keep', 'url' => '/', 'type' => 'link', 'sort_order' => 0]);
        $orphan = $menu->items()->create(['title' => 'Drop', 'url' => '/drop', 'type' => 'link', 'sort_order' => 1]);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/layout/menus/{$menu->id}/reorder", [
                'items' => [
                    ['id' => $keep->id, 'sort_order' => 0, 'parent_id' => null],
                ],
            ])
            ->assertStatus(200);

        $this->assertSoftDeleted('lay_menu_items', ['id' => $orphan->id]);
        $this->assertDatabaseHas('lay_menu_items', ['id' => $keep->id, 'deleted_at' => null]);
    }

    public function test_get_menu_by_location_prefers_active(): void
    {
        Menu::create(['name' => 'Inactive', 'slug' => 'inactive-h', 'location' => 'header', 'is_active' => false]);
        $active = Menu::create(['name' => 'Active', 'slug' => 'active-h', 'location' => 'header', 'is_active' => true]);
        $active->items()->create(['title' => 'Home', 'url' => '/', 'type' => 'link']);

        $response = $this->getJson('/api/v1/public/layout/menus/location/header');

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Active')
            ->assertJsonCount(1, 'data.parent_items');
    }
}
