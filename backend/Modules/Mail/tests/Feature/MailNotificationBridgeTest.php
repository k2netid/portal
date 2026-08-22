<?php

declare(strict_types=1);

namespace Modules\Mail\Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Modules\Core\System\Models\Notification;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Mail\Events\MailMessageFailed;
use Modules\Mail\Exceptions\MailDispatchException;
use Modules\Mail\Services\MailboxIngestService;
use Modules\Mail\Services\MailDispatchService;
use Modules\Mail\Tests\Support\ActivatesMailExtension;
use Tests\TestCase;

class MailNotificationBridgeTest extends TestCase
{
    use ActivatesMailExtension;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissionsAndRoles();
        $this->activateMailExtension();
        $this->user = $this->createSuperAdminUser();
    }

    public function test_send_failure_creates_in_app_notification(): void
    {
        $this->mock(MailDispatchService::class, function ($mock): void {
            $mock->shouldReceive('sendOutbound')
                ->once()
                ->andReturnUsing(function () {
                    event(new MailMessageFailed(
                        'recipient@example.com',
                        'Fail test',
                        'SMTP connection refused',
                        $this->user->id,
                        null,
                    ));

                    throw new MailDispatchException('SMTP connection refused');
                });
        });

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/mail/send', [
                'to' => 'recipient@example.com',
                'subject' => 'Fail test',
                'body' => '<p>Hi</p>',
            ])
            ->assertStatus(502);

        $this->assertDatabaseHas('sys_notifications', [
            'user_id' => $this->user->id,
            'type' => 'error',
            'title' => 'Mail send failed',
        ]);
    }

    public function test_vacation_auto_reply_creates_info_notification(): void
    {
        Setting::set('mail_client_vacation_enabled_user_'.$this->user->id, true, 'boolean', 'mail_client');
        Setting::set('mail_client_vacation_subject_user_'.$this->user->id, 'OOO', 'string', 'mail_client');

        Queue::fake();

        app(MailboxIngestService::class)->ingest($this->user, [
            'sender_email' => 'client@example.com',
            'subject' => 'Hello',
        ]);

        $this->assertTrue(
            Notification::query()
                ->where('user_id', $this->user->id)
                ->where('type', 'info')
                ->where('title', 'Vacation auto-reply sent')
                ->exists(),
        );
    }

    public function test_successful_send_does_not_require_mail_fake_for_notification(): void
    {
        Mail::fake();

        $before = Notification::query()->where('user_id', $this->user->id)->count();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/mail/send', [
                'to' => 'ok@example.com',
                'subject' => 'OK',
                'body' => '<p>Hi</p>',
            ])
            ->assertCreated();

        $after = Notification::query()->where('user_id', $this->user->id)->where('type', 'error')->count();
        $this->assertSame($before, $after);
    }
}
