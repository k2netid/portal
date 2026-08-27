<?php

declare(strict_types=1);

namespace Modules\Library\Tests\Feature;

use Tests\TestCase;

class LibraryPackGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_manage_library_tags_forbidden_when_pack_inactive(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/library/tags')
            ->assertForbidden();
    }

    public function test_manage_library_tags_ok_when_pack_active(): void
    {
        $this->activatePack('library');
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/library/tags')
            ->assertOk();
    }
}
