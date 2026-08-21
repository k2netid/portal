<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\ActivityLog;
use Tests\TestCase;

class ActivityJournalApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_list_activity_logs(): void
    {
        $admin = $this->createAdminUser();

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'login',
            'description' => 'User logged into control plane',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit/Test',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/activity-journal');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'action',
                            'description',
                        ],
                    ],
                ],
            ]);
    }

    public function test_admin_can_get_activity_statistics_and_recent(): void
    {
        $admin = $this->createAdminUser();

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'login',
            'description' => 'User logged into control plane',
            'ip_address' => '127.0.0.1',
        ]);

        $statsResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/activity-journal/statistics');

        $statsResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);

        $recentResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/activity-journal/recent');

        $recentResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_unauthenticated_cannot_access_activity_journal(): void
    {
        $this->getJson('/api/v1/manage/activity-journal')->assertUnauthorized();
        $this->getJson('/api/v1/manage/activity-journal/statistics')->assertUnauthorized();
        $this->getJson('/api/v1/manage/activity-journal/recent')->assertUnauthorized();
        $this->postJson('/api/v1/manage/activity-journal/clear', [])->assertUnauthorized();
    }
}
