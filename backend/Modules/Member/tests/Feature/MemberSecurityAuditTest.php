<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Feature;

use Modules\Core\Security\Models\SecurityLog;
use Modules\Member\Models\Member;
use Modules\Member\Tests\Concerns\SoftensPasswordPolicyForTests;
use Tests\TestCase;

class MemberSecurityAuditTest extends TestCase
{
    use SoftensPasswordPolicyForTests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->softenPasswordPolicyForTests();
        $this->seedPermissionsAndRoles();
        $this->activatePack('member');
    }

    public function test_register_and_login_write_member_security_logs(): void
    {
        $this->postJson('/api/v1/public/member/register', [
            'name' => 'Audit Reader',
            'email' => 'audit-reader@example.com',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ])->assertCreated();

        $this->assertDatabaseHas('sec_logs', [
            'event_type' => 'member_register',
        ]);

        $registerLog = SecurityLog::query()->where('event_type', 'member_register')->latest()->first();
        $this->assertNotNull($registerLog);
        $this->assertNull($registerLog->user_id);
        $this->assertSame('member', $registerLog->metadata['realm'] ?? null);
        $this->assertSame('audit-reader@example.com', $registerLog->metadata['member_email'] ?? null);

        $this->postJson('/api/v1/public/member/login', [
            'email' => 'audit-reader@example.com',
            'password' => 'password12',
        ])->assertOk();

        $loginLog = SecurityLog::query()->where('event_type', 'member_login_success')->latest()->first();
        $this->assertNotNull($loginLog);
        $this->assertSame('member', $loginLog->metadata['realm'] ?? null);
        $this->assertNotEmpty($loginLog->metadata['member_id'] ?? null);
    }

    public function test_failed_login_writes_member_login_failed(): void
    {
        Member::query()->create([
            'name' => 'Fail Reader',
            'email' => 'fail-reader@example.com',
            'password' => 'password12',
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/public/member/login', [
            'email' => 'fail-reader@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();

        $failLog = SecurityLog::query()->where('event_type', 'member_login_failed')->latest()->first();
        $this->assertNotNull($failLog);
        $this->assertSame('member', $failLog->metadata['realm'] ?? null);
        $this->assertSame('fail-reader@example.com', $failLog->metadata['member_email'] ?? null);
    }

    public function test_security_journal_filters_by_realm_member(): void
    {
        SecurityLog::log('login_success', null, '127.0.0.1', 'Console login', ['realm' => 'console']);
        SecurityLog::log('member_login_success', null, '127.0.0.1', 'Member login', [
            'realm' => 'member',
            'member_id' => '00000000-0000-0000-0000-000000000001',
        ]);

        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/security/journal?realm=member&per_page=50');

        $response->assertOk();
        $types = collect($response->json('data.data'))->pluck('event_type')->all();
        $this->assertContains('member_login_success', $types);
        $this->assertNotContains('login_success', $types);
    }

    public function test_member_directory_security_events_endpoint(): void
    {
        $member = Member::query()->create([
            'name' => 'Events Reader',
            'email' => 'events-reader@example.com',
            'password' => 'password12',
            'status' => 'active',
        ]);

        SecurityLog::log('member_login_success', null, '10.0.0.1', 'ok', [
            'realm' => 'member',
            'member_id' => $member->id,
            'member_email' => $member->email,
        ]);
        SecurityLog::log('member_login_success', null, '10.0.0.2', 'other', [
            'realm' => 'member',
            'member_id' => '00000000-0000-0000-0000-000000000099',
        ]);

        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/members/'.$member->id.'/security-events');

        $response->assertOk();
        $events = $response->json('data');
        $this->assertIsArray($events);
        $this->assertCount(1, $events);
        $this->assertSame('member_login_success', $events[0]['event_type']);
        $this->assertSame('10.0.0.1', $events[0]['ip_address']);
    }

    public function test_logout_writes_member_logout_audit(): void
    {
        $register = $this->postJson('/api/v1/public/member/register', [
            'name' => 'Logout Reader',
            'email' => 'logout-reader@example.com',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ])->assertCreated();

        $token = (string) $register->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/member/logout')
            ->assertOk();

        $this->assertDatabaseHas('sec_logs', [
            'event_type' => 'member_logout',
        ]);

        $log = SecurityLog::query()->where('event_type', 'member_logout')->latest()->first();
        $this->assertNotNull($log);
        $this->assertSame('member', $log->metadata['realm'] ?? null);
        $this->assertSame('logout-reader@example.com', $log->metadata['member_email'] ?? null);
    }

    public function test_avatar_upload_writes_member_avatar_uploaded_audit(): void
    {
        $register = $this->postJson('/api/v1/public/member/register', [
            'name' => 'Avatar Reader',
            'email' => 'avatar-reader@example.com',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ])->assertCreated();

        $token = (string) $register->json('data.token');

        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            true,
        );
        $this->assertNotFalse($png);
        $path = tempnam(sys_get_temp_dir(), 'avatar');
        $this->assertNotFalse($path);
        file_put_contents($path, $png);

        $this->withToken($token)
            ->post('/api/v1/member/profile/avatar', [
                'file' => new \Illuminate\Http\UploadedFile($path, 'avatar.png', 'image/png', null, true),
            ])
            ->assertOk();

        @unlink($path);

        $this->assertDatabaseHas('sec_logs', [
            'event_type' => 'member_avatar_uploaded',
        ]);
    }
}
