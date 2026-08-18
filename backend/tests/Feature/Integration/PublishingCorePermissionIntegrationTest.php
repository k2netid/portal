<?php

namespace Tests\Feature\Integration;

use Modules\Content\Media\Models\File;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

/**
 * Integration tests that span System + CMS route groups.
 * Intentionally located outside `Modules/System` to keep System module independent.
 */
class PublishingCorePermissionIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_access_all_protected_endpoints(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        // CMS endpoints
        TestHelpers::assertApiSuccess($this->getJson('/api/v1/manage/publishing/contents'));
        TestHelpers::assertApiSuccess($this->getJson('/api/v1/manage/publishing/settings'));

        // System endpoints
        TestHelpers::assertApiSuccess($this->getJson('/api/v1/manage/system/users'));

        // Media endpoints
        TestHelpers::assertApiSuccess($this->getJson('/api/v1/manage/media'));

        // Library endpoints
        TestHelpers::assertApiSuccess($this->getJson('/api/v1/manage/library/categories'));
    }

    public function test_user_without_permission_cannot_create_content(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/manage/publishing/contents', TestHelpers::getContentData());

        $response->assertStatus(403);
    }

    public function test_user_with_create_content_permission_can_create_content(): void
    {
        $user = $this->createUser();
        $permission = Permission::firstOrCreate(['name' => 'create content', 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/manage/publishing/contents', TestHelpers::getContentData());
        TestHelpers::assertApiSuccess($response, 201);
    }

    public function test_user_without_permission_cannot_manage_media(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $file = File::factory()->create(['author_id' => $user->id]);

        $this->putJson("/api/v1/manage/media/{$file->id}", ['name' => 'Updated Name'])->assertStatus(403);
        $this->deleteJson("/api/v1/manage/media/{$file->id}")->assertStatus(403);
    }

    public function test_user_with_manage_media_permission_can_manage_media(): void
    {
        $user = $this->createUser();
        Permission::firstOrCreate(['name' => 'edit media', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete media', 'guard_name' => 'web']);
        $user->givePermissionTo(['edit media', 'delete media']);
        $this->actingAs($user, 'sanctum');

        $file = File::factory()->create(['author_id' => $user->id]);
        TestHelpers::assertApiSuccess($this->putJson("/api/v1/manage/media/{$file->id}", ['name' => 'Updated Name']));

        $file2 = File::factory()->create(['author_id' => $user->id]);
        TestHelpers::assertApiSuccess($this->deleteJson("/api/v1/manage/media/{$file2->id}"));
    }

    public function test_admin_role_has_expected_permissions(): void
    {
        $admin = $this->createAdminUser();

        foreach ([
            'create content',
            'edit content',
            'delete content',
            'manage media',
            'manage categories',
            'manage users',
        ] as $permissionName) {
            $this->assertTrue($admin->hasPermissionTo($permissionName), "Admin should have {$permissionName} permission");
        }
    }

    public function test_role_assignment_works_correctly(): void
    {
        $user = $this->createUser();
        $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        $permission = Permission::firstOrCreate(['name' => 'edit content', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasPermissionTo('edit content'));
    }
}
