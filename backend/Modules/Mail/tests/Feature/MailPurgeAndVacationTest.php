<?php

declare(strict_types=1);

namespace Modules\Mail\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Mail\Events\MailMessageReceived;
use Modules\Mail\Jobs\SendOutboundMailJob;
use Modules\Mail\Services\MailboxIngestService;
use Modules\Mail\Tests\Support\ActivatesMailExtension;
use Modules\Mail\Tests\Support\CreatesMailMessages;
use Tests\TestCase;

class MailPurgeAndVacationTest extends TestCase
{
    use ActivatesMailExtension;
    use CreatesMailMessages;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissionsAndRoles();
        $this->activateMailExtension();
        $this->user = $this->createSuperAdminUser();
    }

    public function test_move_to_trash_sets_trashed_at_and_purge_removes_old_trash(): void
    {
        Setting::set('mail_client_trash_retention_days', 7, 'integer', 'mail_client');

        $fresh = $this->createMailMessage($this->user, [
            'folder' => 'trash',
            'trashed_at' => now()->subDays(2),
            'subject' => 'Keep me',
        ]);

        $stale = $this->createMailMessage($this->user, [
            'folder' => 'trash',
            'trashed_at' => now()->subDays(10),
            'subject' => 'Purge me',
        ]);

        $this->artisan('mail:purge-trash')->assertSuccessful();

        $this->assertDatabaseHas('sys_mail_messages', ['id' => $fresh->id]);
        $this->assertDatabaseMissing('sys_mail_messages', ['id' => $stale->id]);
    }

    public function test_purge_skips_when_retention_is_zero(): void
    {
        Setting::set('mail_client_trash_retention_days', 0, 'integer', 'mail_client');

        $stale = $this->createMailMessage($this->user, [
            'folder' => 'trash',
            'trashed_at' => now()->subYear(),
            'subject' => 'Keep forever',
        ]);

        $this->artisan('mail:purge-trash')->assertSuccessful();

        $this->assertDatabaseHas('sys_mail_messages', ['id' => $stale->id]);
    }

    public function test_api_trash_sets_trashed_at(): void
    {
        $message = $this->createMailMessage($this->user);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/manage/mail/messages/{$message->id}/trash")
            ->assertOk();

        $message->refresh();
        $this->assertSame('trash', $message->folder);
        $this->assertNotNull($message->trashed_at);
    }

    public function test_ingest_fires_received_event_and_queues_vacation_reply(): void
    {
        Setting::set('mail_client_vacation_enabled_user_'.$this->user->id, true, 'boolean', 'mail_client');
        Setting::set('mail_client_vacation_subject_user_'.$this->user->id, 'OOO', 'string', 'mail_client');
        Setting::set('mail_client_vacation_body_user_'.$this->user->id, 'Back next week', 'string', 'mail_client');

        Event::fake([MailMessageReceived::class]);
        Queue::fake();

        /** @var MailboxIngestService $ingest */
        $ingest = app(MailboxIngestService::class);

        $message = $ingest->ingest($this->user, [
            'sender_name' => 'Client',
            'sender_email' => 'client@example.com',
            'subject' => 'Need help',
            'body' => '<p>Hi</p>',
        ]);

        $this->assertSame('inbox', $message->folder);
        Event::assertDispatched(MailMessageReceived::class);
        Queue::assertPushed(SendOutboundMailJob::class, function (SendOutboundMailJob $job): bool {
            return $job->to === 'client@example.com'
                && $job->subject === 'OOO';
        });
    }

    public function test_vacation_does_not_loop_on_auto_reply_subjects(): void
    {
        Setting::set('mail_client_vacation_enabled_user_'.$this->user->id, true, 'boolean', 'mail_client');

        Queue::fake();

        app(MailboxIngestService::class)->ingest($this->user, [
            'sender_email' => 'bot@example.com',
            'subject' => 'Out of Office Auto-Reply',
        ]);

        Queue::assertNothingPushed();
    }
}
