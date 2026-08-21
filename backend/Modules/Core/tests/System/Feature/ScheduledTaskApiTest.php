<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\ScheduledTask;
use Tests\TestCase;

class ScheduledTaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_list_and_filter_scheduled_tasks(): void
    {
        $admin = $this->createAdminUser();

        ScheduledTask::create([
            'name' => 'Cache Cleanup Task',
            'command' => 'cache:clear',
            'schedule' => '0 0 * * *',
            'description' => 'Clears system cache',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/scheduled-tasks');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'command',
                            'schedule',
                            'is_active',
                        ],
                    ],
                ],
            ]);
    }

    public function test_admin_can_get_allowed_commands_and_prerequisites(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/scheduled-tasks/allowed-commands');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'commands' => [
                        '*' => [
                            'value',
                            'label',
                            'category',
                            'is_recommended',
                            'default_schedule',
                            'prerequisites',
                            'prerequisites_met',
                        ],
                    ],
                    'prerequisites',
                    'base_path',
                ],
            ]);
    }

    public function test_admin_can_create_update_and_delete_scheduled_task(): void
    {
        $admin = $this->createAdminUser();

        // Create
        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/scheduled-tasks', [
                'name' => 'Hourly Cache Clear',
                'command' => 'cache:clear',
                'schedule' => '0 * * * *',
                'description' => 'Clears cache every hour',
                'is_active' => true,
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'Hourly Cache Clear')
            ->assertJsonPath('data.command', 'cache:clear');

        $taskId = (string) $createResponse->json('data.id');

        // Update
        $updateResponse = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/manage/system/scheduled-tasks/'.$taskId, [
                'name' => 'Daily Cache Clear',
                'schedule' => '0 2 * * *',
                'is_active' => false,
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.name', 'Daily Cache Clear')
            ->assertJsonPath('data.is_active', false);

        // Delete
        $deleteResponse = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/manage/system/scheduled-tasks/'.$taskId);

        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('sys_scheduled_tasks', ['id' => $taskId]);
    }

    public function test_admin_can_execute_bulk_actions_on_tasks(): void
    {
        $admin = $this->createAdminUser();

        $t1 = ScheduledTask::create([
            'name' => 'Task 1',
            'command' => 'cache:clear',
            'schedule' => '0 0 * * *',
            'is_active' => false,
        ]);
        $t2 = ScheduledTask::create([
            'name' => 'Task 2',
            'command' => 'optimize:clear',
            'schedule' => '0 1 * * *',
            'is_active' => false,
        ]);

        // Bulk activate
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/scheduled-tasks/bulk', [
                'action' => 'activate',
                'task_ids' => [$t1->id, $t2->id],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.affected_count', 2);

        $this->assertTrue((bool) $t1->fresh()->is_active);
        $this->assertTrue((bool) $t2->fresh()->is_active);

        // Bulk deactivate
        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/scheduled-tasks/bulk', [
                'action' => 'deactivate',
                'task_ids' => [$t1->id, $t2->id],
            ])
            ->assertOk();

        $this->assertFalse((bool) $t1->fresh()->is_active);
        $this->assertFalse((bool) $t2->fresh()->is_active);
    }

    public function test_admin_can_apply_task_presets(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/scheduled-tasks/apply-preset', [
                'preset' => 'recommended',
            ]);

        $response->assertOk();
        $this->assertGreaterThan(0, ScheduledTask::count());
    }

    public function test_admin_can_run_scheduled_task_manually(): void
    {
        $admin = $this->createAdminUser();

        $task = ScheduledTask::create([
            'name' => 'Cache Clear',
            'command' => 'cache:clear',
            'schedule' => '0 0 * * *',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/manage/system/scheduled-tasks/{$task->id}/run");

        $response->assertOk()
            ->assertJsonPath('data.task.status', 'completed');

        $fresh = $task->fresh();
        $this->assertEquals('completed', $fresh->status);
        $this->assertNotNull($fresh->last_run_at);
        $this->assertNotNull($fresh->output);
    }

    public function test_unauthenticated_cannot_access_scheduled_tasks(): void
    {
        $this->getJson('/api/v1/manage/system/scheduled-tasks')->assertUnauthorized();
        $this->getJson('/api/v1/manage/system/scheduled-tasks/allowed-commands')->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/scheduled-tasks/bulk', [])->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/scheduled-tasks/apply-preset', [])->assertUnauthorized();
    }
}
