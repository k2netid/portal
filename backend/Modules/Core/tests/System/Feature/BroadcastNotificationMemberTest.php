<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Services\BroadcastNotificationService;
use Modules\Operational\Member\Models\Member;
use Modules\Operational\Member\Models\MemberNotification;
use Tests\TestCase;

class BroadcastNotificationMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    private function seedMember(string $email): Member
    {
        $package = $this->createPlatformPackage(['id' => 'pkg-'.substr(md5($email), 0, 12)]);
        $subscription = $this->createPlatformSubscription([
            'domain' => str_replace(['@', '.'], '-', $email).'.example.com',
            'package_id' => $package->id,
            'license_key' => 'LIC-'.substr(md5($email), 0, 10),
        ]);
        $user = $this->createUser(['email' => $email]);

        return Member::query()->create([
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'points' => 0,
            'tier' => 'standard',
        ]);
    }

    public function test_broadcast_all_delivers_member_notification(): void
    {
        $this->seedMember('member-broadcast@example.com');

        app(BroadcastNotificationService::class)->deliver([
            'broadcast_id' => '11111111-1111-4111-8111-111111111111',
            'type' => 'warning',
            'title' => 'Server maintenance',
            'message' => 'Tonight 22:00 UTC',
            'target_type' => 'all',
        ]);

        $this->assertDatabaseHas('mbr_notifications', [
            'title' => 'Server maintenance',
            'source_key' => 'broadcast-11111111-1111-4111-8111-111111111111',
        ]);
    }

    public function test_revoke_deletes_member_notifications(): void
    {
        $this->seedMember('revoke-member@example.com');

        app(BroadcastNotificationService::class)->deliver([
            'broadcast_id' => '22222222-2222-4222-8222-222222222222',
            'type' => 'info',
            'title' => 'Revoke me',
            'message' => 'Body',
            'target_type' => 'all',
        ]);

        $this->assertSame(1, MemberNotification::query()->count());

        $row = MemberNotification::query()->first();
        $deleted = app(BroadcastNotificationService::class)->revoke(
            'Revoke me',
            'Body',
            $row->created_at->toIso8601String(),
        );

        $this->assertSame(1, $deleted['member']);
        $this->assertSame(0, MemberNotification::query()->count());
        $this->assertGreaterThan(0, $deleted['console']);
    }
}
