<?php

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\ActivityLog;
use Modules\Core\System\Models\LoginHistory;
use Modules\Core\System\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class JournalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();
        ActivityLog::truncate();
        LoginHistory::truncate();
    }

    /**
     * Test admin can view activity logs list.
     */
    public function test_admin_can_view_activity_logs(): void
    {
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'created',
            'model_type' => 'Modules\Core\System\Models\User',
            'model_id' => $this->admin->id,
            'description' => 'Created User Test',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/activity-journal');

        TestHelpers::assertApiPaginated($response);
        $this->assertCount(1, $response->json('data.data'));
    }

    /**
     * Test admin can view activity logs statistics.
     */
    public function test_admin_can_view_activity_logs_statistics(): void
    {
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'created',
            'model_type' => 'Modules\Core\System\Models\User',
            'model_id' => $this->admin->id,
            'description' => 'Created User Test',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/activity-journal/statistics');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total',
                'today',
                'this_week',
                'active_users',
                'actions_by_type',
                'actions_by_user',
                'actions_by_model',
            ],
        ]);
    }

    /**
     * Test admin can view recent activity logs.
     */
    public function test_admin_can_view_recent_activity_logs(): void
    {
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'created',
            'model_type' => 'Modules\Core\System\Models\User',
            'model_id' => $this->admin->id,
            'description' => 'Created User Test',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/activity-journal/recent?limit=5');

        TestHelpers::assertApiSuccess($response);
        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test admin can export activity logs.
     */
    public function test_admin_can_export_activity_logs(): void
    {
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'created',
            'model_type' => 'Modules\Core\System\Models\User',
            'model_id' => $this->admin->id,
            'description' => 'Created User Test',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/activity-journal/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /**
     * Test admin can clear activity logs.
     */
    public function test_admin_can_clear_activity_logs(): void
    {
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'created',
            'model_type' => 'Modules\Core\System\Models\User',
            'model_id' => $this->admin->id,
            'description' => 'Created User Test',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/activity-journal/clear');

        TestHelpers::assertApiSuccess($response);
        $this->assertEquals(0, ActivityLog::count());
    }

    /**
     * Test admin can view access journal / login history.
     */
    public function test_admin_can_view_access_journal(): void
    {
        LoginHistory::create([
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'login_at' => now(),
            'status' => 'success',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/access-journal');

        TestHelpers::assertApiPaginated($response);
        $this->assertCount(1, $response->json('data.data'));
    }

    /**
     * Test admin can view access journal statistics.
     */
    public function test_admin_can_view_access_journal_statistics(): void
    {
        LoginHistory::create([
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'login_at' => now(),
            'status' => 'success',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/access-journal/statistics');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total_logins',
                'failed_logins',
                'today_logins',
                'unique_ips_today',
                'active_sessions',
                'suspicious_count',
            ],
        ]);
    }

    /**
     * Test admin can view suspicious login activity.
     */
    public function test_admin_can_view_suspicious_logins(): void
    {
        // 3 failed logins from same IP in last 24h will trigger brute_force alert
        for ($i = 0; $i < 3; $i++) {
            LoginHistory::create([
                'user_id' => $this->admin->id,
                'ip_address' => '127.0.0.99',
                'user_agent' => 'Mozilla/5.0',
                'login_at' => now(),
                'status' => 'failed',
                'failure_reason' => 'Invalid credentials',
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/access-journal/suspicious');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'alerts',
                'total',
            ],
        ]);
        $this->assertGreaterThan(0, $response->json('data.total'));
    }

    /**
     * Test admin can export login history.
     */
    public function test_admin_can_export_login_history(): void
    {
        LoginHistory::create([
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'login_at' => now(),
            'status' => 'success',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/access-journal/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /**
     * Test admin can clear login history.
     */
    public function test_admin_can_clear_login_history(): void
    {
        LoginHistory::create([
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'login_at' => now(),
            'status' => 'success',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/access-journal/clear');

        TestHelpers::assertApiSuccess($response);
        $this->assertEquals(0, LoginHistory::count());
    }
}
