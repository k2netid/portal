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

    public function test_user_created_by_default_is_unverified_and_can_be_manually_verified(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $adminRole = Role::findByName('admin', 'web');

        // 1. Create User without is_verified -> should be unverified (email_verified_at is null)
        $createResponse = $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/users', [
                'name' => 'Unverified User',
                'email' => 'unverified@example.com',
                'password' => 'Password123!@#',
                'roles' => [$adminRole->id],
            ]);

        $createResponse->assertCreated();
        $userId = (string) $createResponse->json('data.id');
        $user = User::findOrFail($userId);
        $this->assertNull($user->email_verified_at);

        // 2. Super admin manually verifies the user
        $verifyResponse = $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/users/'.$userId.'/verify');

        $verifyResponse->assertOk();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_user_created_with_is_verified_flag_is_immediately_verified(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $adminRole = Role::findByName('admin', 'web');

        $createResponse = $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/manage/system/users', [
                'name' => 'Verified User',
                'email' => 'verified@example.com',
                'password' => 'Password123!@#',
                'roles' => [$adminRole->id],
                'is_verified' => true,
            ]);

        $createResponse->assertCreated();
        $userId = (string) $createResponse->json('data.id');
        $user = User::findOrFail($userId);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_unauthenticated_cannot_access_roles_or_users(): void
    {
        $this->getJson('/api/v1/manage/system/roles')->assertUnauthorized();
        $this->getJson('/api/v1/manage/system/users')->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/roles', [])->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/users', [])->assertUnauthorized();
    }

    public function test_login_returns_user_with_loaded_permissions_and_roles(): void
    {
        $user = $this->createAdminUser([
            'email' => 'admin_test_rbac@example.com',
            'password' => bcrypt('Password123!@#'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/public/system/auth/login', [
            'email' => 'admin_test_rbac@example.com',
            'password' => 'Password123!@#',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                    'permissions',
                ],
                'redirect_to',
            ],
        ]);
        $this->assertNotEmpty($response->json('data.user.roles'));
        $this->assertIsArray($response->json('data.user.permissions'));
    }

    public function test_member_user_without_permission_cannot_access_roles_or_users(): void
    {
        $memberRole = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        $member = User::factory()->create([
            'email' => 'member_norights@example.com',
            'password' => bcrypt('Password123!@#'),
            'email_verified_at' => now(),
        ]);
        $member->assignRole($memberRole);

        $this->actingAs($member, 'sanctum')->getJson('/api/v1/manage/system/users')->assertForbidden();
        $this->actingAs($member, 'sanctum')->getJson('/api/v1/manage/system/roles')->assertForbidden();
        $this->actingAs($member, 'sanctum')->postJson('/api/v1/manage/system/users', [
            'name' => 'Should Fail',
            'email' => 'fail@example.com',
            'password' => 'Password123!@#',
            'roles' => [$memberRole->id],
        ])->assertForbidden();
    }
}
