<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_run_analytics_cleanup(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/analytics/cleanup');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_deleted' => 0,
                ],
            ]);
    }

    public function test_admin_can_run_analytics_purge_all_with_valid_confirmation(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/analytics/purge-all', [
                'confirmation' => 'RESET_ALL_ANALYTICS',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_deleted' => 0,
                ],
            ]);
    }

    public function test_purge_all_fails_with_invalid_confirmation(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/analytics/purge-all', [
                'confirmation' => 'INVALID_CONFIRMATION',
            ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_get_analytics_overview(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/analytics/overview');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_visits',
                    'unique_visitors',
                    'page_views',
                ],
            ]);
    }

    public function test_unauthenticated_cannot_access_analytics(): void
    {
        $response = $this->postJson('/api/v1/manage/analytics/cleanup');
        $response->assertUnauthorized();
    }
}
