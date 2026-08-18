<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Notification;
use Tests\TestCase;

class NotificationMarkReadApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_mark_notification_read_via_canonical_route(): void
    {
        $admin = $this->createAdminUser();
        $notification = Notification::createForUser(
            $admin->id,
            'info',
            'Test',
            'Body',
        );

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/manage/notifications/'.$notification->id.'/read')
            ->assertOk();

        $notification->refresh();
        $this->assertTrue($notification->is_read);
    }

    public function test_mark_missing_notification_is_idempotent_success(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/manage/notifications/019e5103-7cd4-73a9-9190-bd6bf5bba7a9/read')
            ->assertOk();
    }

    public function test_legacy_system_prefix_route_still_works(): void
    {
        $admin = $this->createAdminUser();
        $notification = Notification::createForUser($admin->id, 'info', 'Legacy', 'Route');

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/manage/system/notifications/'.$notification->id.'/read')
            ->assertOk();
    }
}
