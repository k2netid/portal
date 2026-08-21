<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Infra\Models\Backup;
use Tests\TestCase;

class BackupApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        Storage::fake('local');
    }

    public function test_admin_can_list_backups(): void
    {
        $admin = $this->createAdminUser();

        Backup::create([
            'name' => 'backup_test_1',
            'type' => 'database',
            'status' => 'completed',
            'path' => 'backups/test1.zip',
            'disk' => 'local',
            'size' => 1024,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/backups');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'type',
                        'status',
                        'size',
                        'path',
                    ],
                ],
            ]);
    }

    public function test_admin_can_get_backup_stats(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/backups/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'completed',
                    'failed',
                    'total_size',
                    'latest',
                    'schedule',
                ],
            ]);
    }

    public function test_admin_can_get_and_update_backup_schedule(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/backups/schedule');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'enabled',
                    'frequency',
                    'time',
                    'retention_days',
                    'max_backups',
                ],
            ]);

        $updateResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/backups/schedule', [
                'backup_schedule_enabled' => true,
                'backup_schedule_frequency' => 'weekly',
                'backup_schedule_time' => '03:30',
                'backup_retention_days' => 14,
                'backup_max_count' => 5,
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.frequency', 'weekly')
            ->assertJsonPath('data.time', '03:30')
            ->assertJsonPath('data.retention_days', 14)
            ->assertJsonPath('data.max_backups', 5);
    }

    public function test_admin_can_cleanup_backups(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/backups/cleanup', [
                'retention_days' => 30,
                'max_backups' => 10,
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'deleted',
                ],
            ]);
    }

    public function test_admin_can_delete_backup(): void
    {
        $admin = $this->createAdminUser();

        Storage::disk('local')->put('backups/test_delete.zip', 'dummy content');

        $backup = Backup::create([
            'name' => 'backup_to_delete',
            'type' => 'database',
            'status' => 'completed',
            'path' => 'backups/test_delete.zip',
            'disk' => 'local',
            'size' => 13,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/manage/system/backups/'.$backup->id);

        $response->assertOk();
        $this->assertDatabaseMissing('infra_backups', ['id' => $backup->id]);
    }

    public function test_admin_can_access_infra_prefixed_backup_routes(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/backups')
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/backups/stats')
            ->assertOk();
    }

    public function test_unauthenticated_cannot_access_backups(): void
    {
        $this->getJson('/api/v1/manage/system/backups')->assertUnauthorized();
        $this->getJson('/api/v1/manage/system/backups/stats')->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/backups/schedule', [])->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/backups/cleanup', [])->assertUnauthorized();
        $this->getJson('/api/v1/manage/infra/backups')->assertUnauthorized();
        $this->getJson('/api/v1/manage/infra/backups/stats')->assertUnauthorized();
    }
}
