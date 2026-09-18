<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Security\Feature;

use Modules\Core\System\Models\Role;
use Tests\TestCase;

final class ApiDocsAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_guest_is_redirected_from_docs_ui(): void
    {
        $this->get('/docs/api')
            ->assertRedirect('/auth/console-sign-in');
    }

    public function test_guest_is_forbidden_from_docs_json(): void
    {
        $this->get('/docs/api.json')
            ->assertForbidden();
    }

    public function test_admin_can_view_docs_ui_and_json(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->get('/docs/api')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/docs/api.json')
            ->assertOk();
    }

    public function test_non_admin_is_forbidden(): void
    {
        $member = $this->createUser();
        $member->assignRole(
            Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web'])
        );

        $this->actingAs($member)
            ->get('/docs/api')
            ->assertForbidden();

        $this->actingAs($member)
            ->get('/docs/api.json')
            ->assertForbidden();
    }
}
