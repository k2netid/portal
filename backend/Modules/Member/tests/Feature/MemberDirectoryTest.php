<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Feature;

use Modules\Core\System\Models\Extension;
use Modules\Member\Models\Member;
use Modules\Member\Tests\Concerns\SoftensPasswordPolicyForTests;
use Tests\TestCase;

class MemberDirectoryTest extends TestCase
{
    use SoftensPasswordPolicyForTests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->softenPasswordPolicyForTests();
        $this->seedPermissionsAndRoles();
        $this->activatePack('member');
    }

    public function test_admin_can_list_members(): void
    {
        $admin = $this->createAdminUser();
        Member::query()->create([
            'name' => 'Reader Listed',
            'email' => 'listed@example.com',
            'password' => 'password12',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/members')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['email' => 'listed@example.com']);
    }

    public function test_member_directory_forbidden_when_pack_inactive(): void
    {
        $admin = $this->createAdminUser();
        Extension::query()->where('slug', 'member')->update(['status' => 'inactive']);
        Extension::flushProductActiveMemo();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/members')
            ->assertForbidden();
    }

    public function test_admin_can_update_member_status_with_manage_permission(): void
    {
        $admin = $this->createAdminUser();
        $member = Member::query()->create([
            'name' => 'Reader Toggle',
            'email' => 'toggle@example.com',
            'password' => 'password12',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/manage/members/'.$member->id, ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('mem_members', [
            'id' => $member->id,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_filter_members_by_status_and_verified(): void
    {
        $admin = $this->createAdminUser();
        Member::query()->create([
            'name' => 'Active Verified',
            'email' => 'active-verified@example.com',
            'password' => 'password12',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        Member::query()->create([
            'name' => 'Inactive Unverified',
            'email' => 'inactive-unverified@example.com',
            'password' => 'password12',
            'status' => 'inactive',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/members?status=inactive&verified=unverified')
            ->assertOk()
            ->assertJsonFragment(['email' => 'inactive-unverified@example.com'])
            ->assertJsonMissing(['email' => 'active-verified@example.com']);
    }

    public function test_admin_can_soft_delete_and_restore_member(): void
    {
        $admin = $this->createAdminUser();
        $member = Member::query()->create([
            'name' => 'Reader Trash',
            'email' => 'trash@example.com',
            'password' => 'password12',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/manage/members/'.$member->id)
            ->assertOk();

        $this->assertSoftDeleted('mem_members', ['id' => $member->id]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/members/'.$member->id.'/restore')
            ->assertOk();

        $this->assertDatabaseHas('mem_members', [
            'id' => $member->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_verify_member_email(): void
    {
        $admin = $this->createAdminUser();
        $member = Member::query()->create([
            'name' => 'Reader Verify',
            'email' => 'verify-admin@example.com',
            'password' => 'password12',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/manage/members/'.$member->id, ['verify_email' => true])
            ->assertOk();

        $this->assertNotNull($member->fresh()?->email_verified_at);
    }

    public function test_admin_can_view_member_stats_and_detail(): void
    {
        $admin = $this->createAdminUser();
        $member = Member::query()->create([
            'name' => 'Reader Detail',
            'email' => 'detail@example.com',
            'password' => 'password12',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/members/stats')
            ->assertOk()
            ->assertJsonStructure(['data' => ['total', 'verified', 'unverified', 'recent', 'active']]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/members/'.$member->id)
            ->assertOk()
            ->assertJsonPath('data.email', 'detail@example.com')
            ->assertJsonStructure(['data' => ['activity' => ['bookmarks', 'comments', 'submissions']]]);
    }

    public function test_admin_can_create_member(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/members', [
                'name' => 'Console Created',
                'email' => 'console-created@example.com',
                'password' => 'password12',
                'status' => 'active',
                'verify_email' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'console-created@example.com');

        $this->assertDatabaseHas('mem_members', [
            'email' => 'console-created@example.com',
            'status' => 'active',
        ]);
    }

    public function test_admin_bulk_action_can_deactivate_members(): void
    {
        $admin = $this->createAdminUser();
        $member = Member::query()->create([
            'name' => 'Bulk Reader',
            'email' => 'bulk@example.com',
            'password' => 'password12',
            'status' => 'active',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/members/bulk-action', [
                'ids' => [$member->id],
                'action' => 'deactivate',
            ])
            ->assertOk();

        $this->assertDatabaseHas('mem_members', [
            'id' => $member->id,
            'status' => 'inactive',
        ]);
    }
}
