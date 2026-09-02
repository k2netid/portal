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
            'name' => 'K2NET Footer Produk IT',
            'slug' => 'footer-produk-'.uniqid(),
            'location' => null,
            'is_active' => true,
        ]);

        $item = $menu->items()->create([
            'title' => 'Tokopedia',
            'url' => 'https://www.tokopedia.com/k2net-lama',
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
                        'title' => 'Tokopedia K2NET',
                        'url' => 'https://www.tokopedia.com/k2net-bandung',
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
            ->assertJsonPath('data.0.title', 'Tokopedia K2NET')
            ->assertJsonPath('data.0.url', 'https://www.tokopedia.com/k2net-bandung');

        $item->refresh();
        $this->assertSame('Tokopedia K2NET', $item->title);
        $this->assertSame('https://www.tokopedia.com/k2net-bandung', $item->url);
        $this->assertTrue($item->open_in_new_tab);
    }
}
