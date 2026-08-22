<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class LicenseApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();

        $this->admin = $this->createAdminUser();
    }

    public function test_admin_can_fetch_license_status(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/system/license');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tier',
                    'status',
                    'features',
                ],
            ]);
    }

    public function test_admin_can_access_legacy_system_license_alias(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/system/license');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }
}
