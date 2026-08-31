<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Feature;

use Modules\Core\System\Models\Extension;
use Modules\Member\Models\Member;
use Tests\TestCase;

class MemberDirectoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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
}
