<?php

declare(strict_types=1);

namespace Modules\Mail\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Mail\Tests\Support\ActivatesMailExtension;
use Modules\Mail\Tests\Support\CreatesMailMessages;
use Tests\TestCase;

class MailHonestyHardeningTest extends TestCase
{
    use ActivatesMailExtension;
    use CreatesMailMessages;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->activateMailExtension();
    }

    public function test_snooze_hides_from_inbox_until_wake_command(): void
    {
        $user = $this->createSuperAdminUser();
        $message = $this->createMailMessage($user, [
            'folder' => 'inbox',
            'subject' => 'Snooze me',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/manage/mail/messages/{$message->id}/snooze", [
                'snooze_until' => now()->addHour()->toIso8601String(),
            ])
            ->assertOk();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/manage/mail/messages?folder=inbox')
            ->assertOk()
            ->assertJsonMissing(['id' => $message->id]);

        $message->update(['snoozed_until' => now()->subMinute()]);
        $this->artisan('mail:process-snoozed')->assertSuccessful();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/manage/mail/messages?folder=inbox')
            ->assertOk()
            ->assertJsonFragment(['id' => $message->id]);
    }

    public function test_use_mail_user_cannot_change_global_quota(): void
    {
        Setting::set('mail_client_storage_quota_gb', 15, 'integer', 'mail_client');

        Permission::firstOrCreate(['name' => 'use mail', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'mail-only', 'guard_name' => 'web']);
        $role->syncPermissions(['use mail']);

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/manage/mail/settings', [
                'storage_quota_gb' => 100,
                'per_page' => 25,
            ])
            ->assertOk();

        $this->assertSame(15, (int) Setting::get('mail_client_storage_quota_gb'));
    }

    public function test_cancel_schedule_moves_message_to_drafts(): void
    {
        $user = $this->createSuperAdminUser();
        $message = $this->createMailMessage($user, [
            'folder' => 'scheduled',
            'subject' => '[Scheduled] Launch note',
            'labels' => ['scheduled'],
            'scheduled_at' => now()->addDay(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/manage/mail/messages/{$message->id}/cancel-schedule")
            ->assertOk()
            ->assertJsonPath('data.folder', 'drafts');

        $this->assertDatabaseHas('sys_mail_messages', [
            'id' => $message->id,
            'folder' => 'drafts',
            'scheduled_at' => null,
        ]);
    }

    public function test_blocked_attachment_extension_rejected(): void
    {
        Storage::fake('local');
        Mail::fake();

        $user = $this->createSuperAdminUser();
        $file = UploadedFile::fake()->create('payload.exe', 12, 'application/octet-stream');

        $this->actingAs($user, 'sanctum')
            ->post('/api/v1/manage/mail/send', [
                'to' => 'ops@example.com',
                'subject' => 'Blocked',
                'body' => '<p>nope</p>',
                'attachments' => [$file],
            ], [
                'Accept' => 'application/json',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'MAIL_ATTACHMENT_BLOCKED');
    }

    public function test_blocked_attachment_mime_rejected_even_when_extension_looks_safe(): void
    {
        Storage::fake('local');
        Mail::fake();

        $user = $this->createSuperAdminUser();
        $file = UploadedFile::fake()->create('notes.txt', 12, 'application/x-msdownload');

        $this->actingAs($user, 'sanctum')
            ->post('/api/v1/manage/mail/send', [
                'to' => 'ops@example.com',
                'subject' => 'Blocked mime',
                'body' => '<p>nope</p>',
                'attachments' => [$file],
            ], [
                'Accept' => 'application/json',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'MAIL_ATTACHMENT_BLOCKED');
    }

    public function test_attachment_download_rejects_unowned_path(): void
    {
        $user = $this->createSuperAdminUser();
        $message = $this->createMailMessage($user, [
            'folder' => 'sent',
            'attachments' => [[
                'name' => 'secret.txt',
                'size' => 4,
                'mime' => 'text/plain',
                'path' => '../etc/passwd',
                'disk' => 'local',
            ]],
        ]);

        $this->actingAs($user, 'sanctum')
            ->get("/api/v1/manage/mail/messages/{$message->id}/attachments/0")
            ->assertNotFound();
    }

    public function test_send_rejects_when_storage_quota_exceeded(): void
    {
        Mail::fake();
        Setting::set('mail_client_storage_quota_gb', 0, 'integer', 'mail_client');

        $user = $this->createSuperAdminUser();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/manage/mail/send', [
                'to' => 'ops@example.com',
                'subject' => 'Quota',
                'body' => '<p>hi</p>',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'MAIL_QUOTA_EXCEEDED');
    }

    public function test_folder_counts_include_archive_and_scheduled(): void
    {
        $user = $this->createSuperAdminUser();
        $this->createMailMessage($user, ['folder' => 'archive', 'subject' => 'Archived']);
        $this->createMailMessage($user, [
            'folder' => 'scheduled',
            'subject' => '[Scheduled] Later',
            'scheduled_at' => now()->addDay(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/manage/mail/messages?folder=inbox')
            ->assertOk()
            ->assertJsonPath('data.folder_counts.archive', 1)
            ->assertJsonPath('data.folder_counts.scheduled', 1);
    }
}
