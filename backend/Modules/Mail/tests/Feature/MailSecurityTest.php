<?php

declare(strict_types=1);

namespace Modules\Mail\Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\User;
use Modules\Mail\Exceptions\MailDispatchException;
use Modules\Mail\Models\MailMessage;
use Modules\Mail\Services\MailDispatchService;
use Modules\Mail\Tests\Support\ActivatesMailExtension;
use Modules\Mail\Tests\Support\CreatesMailMessages;
use Tests\TestCase;

class MailSecurityTest extends TestCase
{
    use ActivatesMailExtension;
    use CreatesMailMessages;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissionsAndRoles();
        $this->activateMailExtension();
        $this->admin = $this->createSuperAdminUser();
    }

    public function test_user_cannot_access_another_users_message(): void
    {
        $owner = $this->admin;
        $intruder = $this->createAdminUser();

        $message = $this->createMailMessage($owner, [
            'subject' => 'Private inbox item',
        ]);

        $this->actingAs($intruder, 'sanctum')
            ->getJson("/api/v1/manage/mail/messages/{$message->id}")
            ->assertNotFound();
    }

    public function test_inactive_mail_extension_returns_forbidden(): void
    {
        Extension::query()->where('slug', 'mail')->update(['status' => 'inactive']);

        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/mail/messages')
            ->assertForbidden();
    }

    public function test_send_failure_returns_502(): void
    {
        $this->mock(MailDispatchService::class, function ($mock): void {
            $mock->shouldReceive('sendOutbound')
                ->once()
                ->andThrow(new MailDispatchException('SMTP connection refused'));
        });

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/mail/send', [
                'to' => 'recipient@example.com',
                'subject' => 'Fail test',
                'body' => '<p>Hi</p>',
            ])
            ->assertStatus(502)
            ->assertJsonPath('error_code', 'MAIL_SEND_FAILED');
    }

    public function test_test_connection_rejects_private_host_ssrf(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/mail/accounts/test', [
                'host' => '127.0.0.1',
                'port' => 25,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['host']);
    }

    public function test_user_without_manage_system_permission_is_forbidden(): void
    {
        $user = $this->createViewerUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/manage/mail/messages')
            ->assertForbidden();
    }

    public function test_scheduled_command_only_dispatches_due_messages(): void
    {
        Mail::fake();

        $future = $this->createMailMessage($this->admin, [
            'folder' => 'scheduled',
            'subject' => '[Scheduled] Future mail',
            'scheduled_at' => now()->addHour(),
            'labels' => ['scheduled'],
        ]);

        $due = $this->createMailMessage($this->admin, [
            'folder' => 'scheduled',
            'subject' => '[Scheduled] Due mail',
            'recipients' => ['due@example.com'],
            'scheduled_at' => now()->subMinute(),
            'labels' => ['scheduled'],
        ]);

        $this->artisan('mail:process-scheduled')->assertSuccessful();

        $this->assertDatabaseHas('sys_mail_messages', [
            'id' => $future->id,
            'folder' => 'scheduled',
        ]);

        $this->assertDatabaseHas('sys_mail_messages', [
            'id' => $due->id,
            'folder' => 'sent',
            'subject' => 'Due mail',
        ]);
    }
}
