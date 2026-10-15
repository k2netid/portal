<?php

declare(strict_types=1);

namespace Modules\Layout\Tests\Feature;

use Modules\Layout\Models\Menu;
use Tests\TestCase;

class MenuItemPersistTest extends TestCase
{
    public function test_updating_menu_item_persists_title_and_url(): void
    {
        $this->activatePack('layout');
        $this->seedPermissionsAndRoles();

        $menu = Menu::query()->create([
            'name' => 'Header Nav',
            'slug' => 'header-nav-'.uniqid(),
            'location' => 'header',
            'is_active' => true,
        ]);

        $item = $menu->items()->create([
            'title' => 'Kontak NOC',
            'url' => '/#contact',
            'type' => 'custom',
            'sort_order' => 0,
        ]);

        $user = $this->createSuperAdminUser();
        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/manage/layout/menus/'.$menu->id.'/items/'.$item->id, [
                'title' => 'Kontak',
                'url' => '/contact',
                'type' => 'custom',
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Kontak')
            ->assertJsonPath('data.url', '/contact');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/manage/layout/menus/'.$menu->id.'/items')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Kontak')
            ->assertJsonPath('data.0.url', '/contact');
    }

    public function test_reorder_does_not_reset_item_title(): void
    {
        $this->activatePack('layout');
        $this->seedPermissionsAndRoles();

        $menu = Menu::query()->create([
            'name' => 'Header Nav',
            'slug' => 'header-nav-reorder-'.uniqid(),
            'location' => 'header',
            'is_active' => true,
        ]);

        $item = $menu->items()->create([
            'title' => 'Kontak',
            'url' => '/contact',
            'type' => 'custom',
            'sort_order' => 0,
        ]);

        $user = $this->createSuperAdminUser();
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/manage/layout/menus/'.$menu->id.'/reorder', [
                'items' => [
                    [
                        'id' => $item->id,
                        'sort_order' => 1,
                        'parent_id' => null,
                    ],
                ],
            ])
            ->assertOk();

        $item->refresh();
        $this->assertSame('Kontak', $item->title);
        $this->assertSame('/contact', $item->url);
        $this->assertSame(1, $item->sort_order);
    }

    public function test_sync_items_persists_title_and_url_for_existing_items(): void
    {
        $this->activatePack('layout');
        $this->seedPermissionsAndRoles();

        $menu = Menu::query()->create([
            'name' => 'Footer Produk IT',
            'slug' => 'footer-produk-'.uniqid(),
            'location' => null,
            'is_active' => true,
        ]);

        $item = $menu->items()->create([
            'title' => 'Tokopedia',
            'url' => 'https://www.tokopedia.com/old-store',
            'type' => 'custom',
            'sort_order' => 1,
        ]);

        $user = $this->createSuperAdminUser();
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/manage/layout/menus/'.$menu->id.'/items/sync', [
                'items' => [
                    [
                        'id' => $item->id,
                        'client_id' => $item->id,
                        'title' => 'Tokopedia Portal',
                        'url' => 'https://www.tokopedia.com/portal-store',
                        'type' => 'custom',
                        'parent_id' => null,
                        'sort_order' => 1,
                        'open_in_new_tab' => true,
                        'metadata' => [
                            'badge_color' => 'primary',
                            'mega_menu_layout' => 'default',
                        ],
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Tokopedia Portal')
            ->assertJsonPath('data.0.url', 'https://www.tokopedia.com/portal-store');

        $item->refresh();
        $this->assertSame('Tokopedia Portal', $item->title);
        $this->assertSame('https://www.tokopedia.com/portal-store', $item->url);
        $this->assertTrue($item->open_in_new_tab);
    }

    public function test_sync_items_allows_hash_url_and_anchor_links(): void
    {
        $this->activatePack('layout');
        $this->seedPermissionsAndRoles();

        $menu = Menu::query()->create([
            'name' => 'Mega Menu Nav',
            'slug' => 'mega-menu-nav-'.uniqid(),
            'location' => 'header',
            'is_active' => true,
        ]);

        $user = $this->createSuperAdminUser();
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/manage/layout/menus/'.$menu->id.'/items/sync', [
                'items' => [
                    [
                        'id' => null,
                        'client_id' => 'temp_parent_1',
                        'title' => 'Program Keahlian',
                        'url' => '#',
                        'type' => 'custom',
                        'parent_id' => null,
                        'sort_order' => 0,
                        'metadata' => [
                            'mega_menu_layout' => 'grid-3',
                        ],
                    ],
                    [
                        'id' => null,
                        'client_id' => 'temp_group_1',
                        'title' => 'Teknologi & Rekayasa',
                        'url' => '#jurusan-teknologi',
                        'type' => 'column_group',
                        'parent_id' => 'temp_parent_1',
                        'sort_order' => 0,
                    ],
                    [
                        'id' => null,
                        'client_id' => 'temp_link_1',
                        'title' => 'TKRO',
                        'url' => '/blog/tkro',
                        'type' => 'custom',
                        'parent_id' => 'temp_group_1',
                        'sort_order' => 0,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $this->assertDatabaseHas('lay_menu_items', [
            'menu_id' => $menu->id,
            'title' => 'Program Keahlian',
            'url' => '#',
        ]);

        $this->assertDatabaseHas('lay_menu_items', [
            'menu_id' => $menu->id,
            'title' => 'Teknologi & Rekayasa',
            'url' => '#jurusan-teknologi',
        ]);

        // Verify public API loads all 3 levels
        $response = $this->getJson('/api/v1/public/layout/menus/'.$menu->id);
        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotEmpty($data['parent_items']);
        $this->assertSame('Program Keahlian', $data['parent_items'][0]['title']);
        $this->assertNotEmpty($data['parent_items'][0]['children']);
        $this->assertSame('Teknologi & Rekayasa', $data['parent_items'][0]['children'][0]['title']);
        $this->assertNotEmpty($data['parent_items'][0]['children'][0]['children']);
        $this->assertSame('TKRO', $data['parent_items'][0]['children'][0]['children'][0]['title']);
    }
}
