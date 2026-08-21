<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Notification;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_user_can_list_notifications_and_read_them(): void
    {
        $admin = $this->createAdminUser();

        // Seed a notification for the user
        $notification = Notification::create([
            'user_id' => $admin->id,
            'title' => 'System Update Available',
            'message' => 'New version 2.0 has been deployed',
            'type' => 'info',
            'is_read' => false,
        ]);

        // 1. List
        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'title', 'message', 'type'],
                    ],
                ],
            ]);

        // 2. Unread Count
        $unreadResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/notifications/unread-count');

        $unreadResponse->assertOk()
            ->assertJsonPath('data.count', 1);

        // 3. Mark Read
        $readResponse = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/manage/notifications/'.$notification->id.'/read');

        $readResponse->assertOk();
        $this->assertTrue((bool) $notification->fresh()->is_read);

        // 4. Delete
        $deleteResponse = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/manage/notifications/'.$notification->id);

        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('sys_notifications', ['id' => $notification->id]);
    }

    public function test_admin_can_broadcast_notification(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/notifications/broadcast', [
                'title' => 'Maintenance Scheduled',
                'message' => 'Database backup and migration at 00:00 UTC',
                'type' => 'warning',
                'target_type' => 'all',
            ]);

        $response->assertOk();
    }

    public function test_unauthenticated_cannot_access_notifications(): void
    {
        $this->getJson('/api/v1/manage/notifications')->assertUnauthorized();
        $this->postJson('/api/v1/manage/notifications/broadcast', [])->assertUnauthorized();
    }
}
