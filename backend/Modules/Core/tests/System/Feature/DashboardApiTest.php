<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_fetch_admin_dashboard_metrics(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/dashboard/admin?days=7');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'stats' => [
                        'users',
                        'system',
                    ],
                    'charts' => [
                        'userActivity',
                        'systemActivity',
                    ],
                ],
            ]);

        $traffic = $response->json('data.charts.systemActivity');
        $this->assertIsArray($traffic);
        $this->assertCount(7, $traffic);
    }

    public function test_creator_can_fetch_creator_dashboard(): void
    {
        $creator = $this->createCreatorUser();

        $response = $this->actingAs($creator, 'sanctum')
            ->getJson('/api/v1/dashboard/creator?days=7');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'stats' => [
                        'users',
                    ],
                    'charts' => [
                        'userActivity',
                    ],
                ],
            ]);
    }

    public function test_viewer_can_fetch_viewer_dashboard(): void
    {
        $viewer = $this->createViewerUser();

        $response = $this->actingAs($viewer, 'sanctum')
            ->getJson('/api/v1/dashboard/viewer');

        $response->assertOk();
    }

    public function test_unauthenticated_cannot_access_dashboard(): void
    {
        $this->getJson('/api/v1/dashboard/admin')->assertUnauthorized();
        $this->getJson('/api/v1/dashboard/creator')->assertUnauthorized();
    }
}
