<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\CapabilityRegistryService;
use Tests\TestCase;

class CapabilityRegistryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_user_with_view_roles_can_get_capabilities_registry_tree(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/roles/capabilities');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'slug',
                        'name',
                        'description',
                        'is_core',
                        'family',
                        'features' => [
                            '*' => [
                                'slug',
                                'name',
                                'description',
                                'actions' => [
                                    '*' => [
                                        'key',
                                        'permission',
                                        'label',
                                        'action_type',
                                        'default_roles',
                                        'oauth_scope',
                                        'is_dangerous',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $modules = $response->json('data');
        $this->assertIsArray($modules);
        $this->assertGreaterThanOrEqual(1, count($modules));
    }

    public function test_user_with_view_roles_can_get_role_defaults_diff(): void
    {
        $admin = $this->createAdminUser();
        $authorRole = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);

        // Give author an extra permission not in default (e.g. manage settings)
        $authorRole->givePermissionTo('view settings');

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/manage/system/roles/{$authorRole->id}/defaults-diff");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current',
                    'defaults',
                    'to_add',
                    'to_remove',
                    'is_in_sync',
                ],
            ]);

        $data = $response->json('data');
        // "view settings" was given to author, which is not in author defaults, so it should be in to_remove
        $this->assertContains('view settings', $data['to_remove']);
        $this->assertFalse($data['is_in_sync']);
    }

    public function test_admin_can_reset_single_role_to_manifest_defaults(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $authorRole = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);

        // Tamper with author permissions: add view settings and remove view content
        $authorRole->syncPermissions(['view settings']);

        $response = $this->actingAs($superAdmin, 'sanctum')
            ->postJson("/api/v1/manage/system/roles/{$authorRole->id}/reset-defaults");

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Author should now have standard defaults restored (e.g. view content) and NOT view settings
        $refreshed = $authorRole->fresh();
        $this->assertFalse($refreshed->hasPermissionTo('view settings'));
        $this->assertTrue($refreshed->hasPermissionTo('view content'));
    }

    public function test_cannot_reset_super_role(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $superRole = Role::firstOrCreate(['name' => 'super', 'guard_name' => 'web']);

        $response = $this->actingAs($superAdmin, 'sanctum')
            ->postJson("/api/v1/manage/system/roles/{$superRole->id}/reset-defaults");

        $response->assertForbidden();
    }

    public function test_admin_can_reset_all_roles_to_manifest_defaults(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);

        $response = $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/roles/reset-all-defaults');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $rolesReset = $response->json('data');
        $this->assertIsArray($rolesReset);
        $this->assertArrayHasKey('admin', $rolesReset);
        $this->assertArrayHasKey('editor', $rolesReset);
        $this->assertArrayHasKey('author', $rolesReset);
    }

    public function test_unauthorized_user_cannot_access_or_reset_capabilities(): void
    {
        $memberRole = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $member = User::factory()->create([
            'email' => 'regular_member@example.com',
            'password' => bcrypt('Password123!@#'),
            'email_verified_at' => now(),
        ]);
        $member->assignRole($memberRole);

        $this->actingAs($member, 'sanctum')
            ->getJson('/api/v1/manage/system/roles/capabilities')
            ->assertForbidden();

        $this->actingAs($member, 'sanctum')
            ->postJson('/api/v1/manage/system/roles/reset-all-defaults')
            ->assertForbidden();
    }
}
