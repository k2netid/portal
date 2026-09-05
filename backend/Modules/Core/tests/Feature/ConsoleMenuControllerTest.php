<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Feature;

use Modules\Core\System\Models\ConsoleMenu;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class ConsoleMenuControllerTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
    }

    public function test_admin_can_list_console_menus_and_auto_seed(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/manage/console-menus');

        $response->assertOk()
            ->assertJsonPath('success', true);

        // 10 root groups + children (categories removed, consolidated into Content Studio tabs)
        $this->assertDatabaseCount('sys_console_menus', 54);
        $this->assertDatabaseMissing('sys_console_menus', [
            'route_name' => 'categories.index',
        ]);
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'contents.index',
            'extension_slug' => 'publishing',
        ]);
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'tags',
            'name' => 'General Tags',
            'label_key' => 'library.navigation.menu.generalTags',
            'extension_slug' => 'library',
        ]);
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'media',
            'extension_slug' => 'media',
        ]);
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'menus',
            'extension_slug' => 'layout',
        ]);
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'themes',
            'extension_slug' => 'layout',
        ]);
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'redirects',
            'extension_slug' => 'layout',
        ]);
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'forms',
            'extension_slug' => 'forms',
        ]);
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'members.index',
            'extension_slug' => 'member',
        ]);
    }

    public function test_index_soft_syncs_missing_pack_menu_defaults(): void
    {
        ConsoleMenu::seedDefaults();
        ConsoleMenu::query()
            ->whereIn('extension_slug', ['publishing', 'library', 'layout'])
            ->orWhereIn('group_slug', ['editorial', 'library'])
            ->delete();

        $this->assertDatabaseMissing('sys_console_menus', ['route_name' => 'contents.index']);
        $this->assertDatabaseMissing('sys_console_menus', ['route_name' => 'tags']);
        $this->assertDatabaseMissing('sys_console_menus', ['route_name' => 'menus']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/manage/console-menus');

        $response->assertOk();
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'contents.index',
            'extension_slug' => 'publishing',
        ]);
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'tags',
            'extension_slug' => 'library',
        ]);
        $this->assertDatabaseHas('sys_console_menus', [
            'route_name' => 'menus',
            'extension_slug' => 'layout',
        ]);
    }

    public function test_admin_can_create_menu_item(): void
    {
        $parent = ConsoleMenu::create([
            'group_slug' => 'custom',
            'name' => 'Custom Group',
            'order' => 1,
            'is_visible' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/console-menus', [
                'parent_id' => $parent->id,
                'group_slug' => 'custom',
                'name' => 'Custom Item',
                'route_name' => 'custom.index',
                'icon' => 'sparkles',
                'permission' => 'manage system',
                'badge_text' => 'HOT',
                'badge_variant' => 'rose',
                'order' => 1,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Custom Item')
            ->assertJsonPath('data.badge_text', 'HOT');

        $this->assertDatabaseHas('sys_console_menus', [
            'name' => 'Custom Item',
            'group_slug' => 'custom',
        ]);
    }

    public function test_admin_can_update_menu_item(): void
    {
        $menu = ConsoleMenu::create([
            'group_slug' => 'system_config',
            'name' => 'Old Title',
            'order' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/manage/console-menus/{$menu->id}", [
                'name' => 'Updated Title',
                'badge_text' => 'PRO',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Title')
            ->assertJsonPath('data.badge_text', 'PRO');

        $this->assertDatabaseHas('sys_console_menus', [
            'id' => $menu->id,
            'name' => 'Updated Title',
        ]);
    }

    public function test_admin_can_delete_menu_item(): void
    {
        $menu = ConsoleMenu::create([
            'group_slug' => 'system_config',
            'name' => 'To be deleted',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/manage/console-menus/{$menu->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('sys_console_menus', [
            'id' => $menu->id,
        ]);
    }

    public function test_admin_can_reorder_menus(): void
    {
        $item1 = ConsoleMenu::create(['group_slug' => 'g1', 'name' => 'Item 1', 'order' => 1]);
        $item2 = ConsoleMenu::create(['group_slug' => 'g1', 'name' => 'Item 2', 'order' => 2]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/console-menus/reorder', [
                'items' => [
                    ['id' => $item1->id, 'order' => 2, 'parent_id' => null],
                    ['id' => $item2->id, 'order' => 1, 'parent_id' => null],
                ],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('sys_console_menus', ['id' => $item1->id, 'order' => 2]);
        $this->assertDatabaseHas('sys_console_menus', ['id' => $item2->id, 'order' => 1]);
    }

    public function test_admin_can_reset_menus_to_defaults(): void
    {
        // Add junk item
        ConsoleMenu::create(['group_slug' => 'junk', 'name' => 'Junk Item']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/console-menus/reset');

        $response->assertOk();

        $this->assertDatabaseMissing('sys_console_menus', ['name' => 'Junk Item']);
        $this->assertDatabaseHas('sys_console_menus', ['name' => 'Configuration']);
    }

    public function test_menu_groups_include_editorial_and_insight(): void
    {
        ConsoleMenu::seedDefaults();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/manage/console-menus/groups');

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug')->all();
        $this->assertContains('all', $slugs);
        $this->assertContains('editorial', $slugs);
        $this->assertContains('insight', $slugs);
        $this->assertContains('library', $slugs);
        $this->assertContains('audience', $slugs);
    }

    public function test_unauthenticated_cannot_access_console_menus(): void
    {
        $this->getJson('/api/v1/manage/console-menus')
            ->assertUnauthorized();
    }
}
