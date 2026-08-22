<?php

namespace Modules\Mail\Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Modules\Core\System\Models\User;
use Modules\Mail\Models\MailMessage;
use Modules\Mail\Tests\Support\ActivatesMailExtension;
use Modules\Mail\Tests\Support\CreatesMailMessages;
use Tests\TestCase;

class MailControllerTest extends TestCase
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

    public function test_admin_can_list_mail_messages_and_folder_counts(): void
    {
        $this->createMailMessage($this->user, [
            'sender_name' => 'Support Team',
            'sender_email' => 'support@example.com',
            'subject' => 'Welcome to Jejakawan',
            'snippet' => 'Welcome message snippet',
            'body' => '<p>Welcome!</p>',
            'is_starred' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/manage/mail/messages?folder=inbox');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'items',
                    'total',
                    'folder_counts' => [
                        'inbox',
                        'sent',
                        'drafts',
                        'trash',
                        'spam',
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.items'));
        $this->assertGreaterThanOrEqual(1, $response->json('data.folder_counts.inbox'));
    }

    public function test_admin_can_view_mail_message_and_mark_as_read(): void
    {
        $message = $this->createMailMessage($this->user, [
            'sender_name' => 'Alerts Bot',
            'sender_email' => 'alerts@example.com',
            'subject' => 'System Alert',
            'body' => '<p>Alert message body</p>',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/manage/mail/messages/{$message->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_read', true);

        $this->assertDatabaseHas('sys_mail_messages', [
            'id' => $message->id,
            'is_read' => true,
        ]);
    }

    public function test_admin_can_send_email_and_store_in_sent_folder(): void
    {
        Mail::fake();

        $payload = [
            'to' => 'recipient@example.com',
            'subject' => 'Test Outgoing Email',
            'body' => '<p>Hello from test suit!</p>',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/mail/send', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.folder', 'sent')
            ->assertJsonPath('data.subject', 'Test Outgoing Email');

        $this->assertDatabaseHas('sys_mail_messages', [
            'user_id' => $this->user->id,
            'folder' => 'sent',
            'subject' => 'Test Outgoing Email',
        ]);
    }

    public function test_admin_can_move_message_and_toggle_label(): void
    {
        $message = $this->createMailMessage($this->user, [
            'subject' => 'Move and Tag Test',
            'labels' => [],
        ]);

        $moveRes = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/manage/mail/messages/{$message->id}/move", ['folder' => 'spam']);

        $moveRes->assertOk();
        $this->assertDatabaseHas('sys_mail_messages', ['id' => $message->id, 'folder' => 'spam']);

        $labelRes = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/manage/mail/messages/{$message->id}/label", ['label' => 'urgent']);

        $labelRes->assertOk();
        $this->assertDatabaseHas('sys_mail_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_admin_can_toggle_star_and_move_to_trash(): void
    {
        $message = $this->createMailMessage($this->user, [
            'sender_name' => 'Newsletter',
            'sender_email' => 'news@example.com',
            'subject' => 'Monthly Newsletter',
            'body' => '<p>Newsletter content</p>',
            'is_read' => true,
            'is_starred' => false,
        ]);

        $starRes = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/manage/mail/messages/{$message->id}/star");

        $starRes->assertOk()
            ->assertJsonPath('data.is_starred', true);

        $trashRes = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/manage/mail/messages/{$message->id}/trash");

        $trashRes->assertOk();

        $this->assertDatabaseHas('sys_mail_messages', [
            'id' => $message->id,
            'folder' => 'trash',
        ]);

        $restoreRes = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/manage/mail/messages/{$message->id}/restore");

        $restoreRes->assertOk();

        $this->assertDatabaseHas('sys_mail_messages', [
            'id' => $message->id,
            'folder' => 'inbox',
        ]);

        $deleteRes = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/manage/mail/messages/{$message->id}");

        $deleteRes->assertOk();

        $this->assertDatabaseMissing('sys_mail_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_admin_can_manage_settings_and_empty_trash(): void
    {
        $settingsRes = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/mail/settings', [
                'signature' => 'Kind regards, Admin',
                'vacation_enabled' => true,
                'auto_check_interval' => 5,
            ]);

        $settingsRes->assertOk();

        $getSettingsRes = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/manage/mail/settings');

        $getSettingsRes->assertOk()
            ->assertJsonPath('data.vacation_enabled', true);

        $this->createMailMessage($this->user, [
            'folder' => 'trash',
            'sender_name' => 'Old Mail',
            'sender_email' => 'old@example.com',
            'subject' => 'To be emptied',
            'body' => '<p>Dust</p>',
        ]);

        $emptyRes = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/manage/mail/trash/empty');

        $emptyRes->assertOk();
        $this->assertDatabaseMissing('sys_mail_messages', ['subject' => 'To be emptied']);
    }

    public function test_admin_can_save_draft_and_process_scheduled_mail(): void
    {
        $draftRes = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/mail/messages/draft', [
                'to' => 'partner@example.com',
                'subject' => 'Draft Proposal',
                'body' => '<p>Proposal draft content</p>',
            ]);

        $draftRes->assertStatus(201);
        $this->assertDatabaseHas('sys_mail_messages', [
            'user_id' => $this->user->id,
            'folder' => 'drafts',
            'subject' => 'Draft Proposal',
        ]);

        Mail::fake();

        $scheduleRes = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/mail/messages/schedule', [
                'to' => 'investor@example.com',
                'subject' => 'Quarterly Investor Update',
                'body' => '<p>Growth report</p>',
                'scheduled_at' => now()->addMinutes(5)->toIso8601String(),
            ]);

        $scheduleRes->assertStatus(201);
        $this->assertDatabaseHas('sys_mail_messages', [
            'folder' => 'scheduled',
            'subject' => '[Scheduled] Quarterly Investor Update',
        ]);

        MailMessage::query()
            ->where('subject', '[Scheduled] Quarterly Investor Update')
            ->update(['scheduled_at' => now()->subMinute()]);

        $this->artisan('mail:process-scheduled')
            ->expectsOutput('Checking for scheduled emails to dispatch...')
            ->assertSuccessful();

        $this->assertDatabaseHas('sys_mail_messages', [
            'folder' => 'sent',
            'subject' => 'Quarterly Investor Update',
        ]);
    }

    public function test_unauthenticated_cannot_access_mail(): void
    {
        $this->getJson('/api/v1/manage/mail/messages')
            ->assertUnauthorized();
    }
}
