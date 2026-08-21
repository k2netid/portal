<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class RolesAndUsersApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_list_roles_and_permissions(): void
    {
        $admin = $this->createAdminUser();

        $rolesResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/roles');

        $rolesResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);

        $permissionsResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/roles/permissions');

        $permissionsResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_admin_can_create_update_and_delete_role(): void
    {
        $superAdmin = $this->createSuperAdminUser();

        // Create
        $createResponse = $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/roles', [
                'name' => 'Auditor Role',
                'description' => 'System auditor role',
                'permissions' => ['view system'],
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'Auditor Role');

        $roleId = (string) $createResponse->json('data.id');

        // Update
        $updateResponse = $this->actingAs($superAdmin, 'sanctum')
            ->putJson('/api/v1/manage/system/roles/'.$roleId, [
                'name' => 'Senior Auditor Role',
                'permissions' => ['view system'],
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.name', 'Senior Auditor Role');

        // Delete
        $deleteResponse = $this->actingAs($superAdmin, 'sanctum')
            ->deleteJson('/api/v1/manage/system/roles/'.$roleId);

        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('srv_auth_roles', ['id' => $roleId]);
    }

    public function test_admin_can_manage_users_and_force_logout(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $adminRole = Role::findByName('admin', 'web');

        // Create User
        $createResponse = $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/users', [
                'name' => 'John Operator',
                'email' => 'operator.john@example.com',
                'password' => 'Password123!@#',
                'roles' => [$adminRole->id],
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'John Operator')
            ->assertJsonPath('data.email', 'operator.john@example.com');

        $userId = (string) $createResponse->json('data.id');
        $user = User::findOrFail($userId);

        // Create a token for the user to simulate active session
        $user->createToken('test_session');
        $this->assertCount(1, $user->tokens);

        // Force logout by Super Admin
        $logoutResponse = $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/users/'.$userId.'/force-logout');

        $logoutResponse->assertOk();
        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_unauthenticated_cannot_access_roles_or_users(): void
    {
        $this->getJson('/api/v1/manage/system/roles')->assertUnauthorized();
        $this->getJson('/api/v1/manage/system/users')->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/roles', [])->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/users', [])->assertUnauthorized();
    }
}
