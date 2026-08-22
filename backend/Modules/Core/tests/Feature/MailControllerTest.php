<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Modules\Core\System\Models\MailMessage;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class MailControllerTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
    }

    public function test_admin_can_list_mail_messages_and_folder_counts(): void
    {
        MailMessage::create([
            'folder' => 'inbox',
            'sender_name' => 'Support Team',
            'sender_email' => 'support@example.com',
            'recipients' => ['admin@jejakawan.com'],
            'subject' => 'Welcome to Jejakawan',
            'snippet' => 'Welcome message snippet',
            'body' => '<p>Welcome!</p>',
            'is_read' => false,
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
        $message = MailMessage::create([
            'folder' => 'inbox',
            'sender_name' => 'Alerts Bot',
            'sender_email' => 'alerts@example.com',
            'recipients' => ['admin@jejakawan.com'],
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
            'folder' => 'sent',
            'subject' => 'Test Outgoing Email',
        ]);
    }

    public function test_admin_can_move_message_and_toggle_label(): void
    {
        $message = MailMessage::create([
            'folder' => 'inbox',
            'sender_name' => 'Support',
            'sender_email' => 'support@example.com',
            'recipients' => ['admin@jejakawan.com'],
            'subject' => 'Move and Tag Test',
            'body' => '<p>Content</p>',
            'labels' => [],
        ]);

        // Move to spam
        $moveRes = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/manage/mail/messages/{$message->id}/move", ['folder' => 'spam']);

        $moveRes->assertOk();
        $this->assertDatabaseHas('sys_mail_messages', ['id' => $message->id, 'folder' => 'spam']);

        // Add label
        $labelRes = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/manage/mail/messages/{$message->id}/label", ['label' => 'urgent']);

        $labelRes->assertOk();
        $this->assertDatabaseHas('sys_mail_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_admin_can_toggle_star_and_move_to_trash(): void
    {
        $message = MailMessage::create([
            'folder' => 'inbox',
            'sender_name' => 'Newsletter',
            'sender_email' => 'news@example.com',
            'recipients' => ['admin@jejakawan.com'],
            'subject' => 'Monthly Newsletter',
            'body' => '<p>Newsletter content</p>',
            'is_read' => true,
            'is_starred' => false,
        ]);

        // Toggle Star
        $starRes = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/manage/mail/messages/{$message->id}/star");

        $starRes->assertOk()
            ->assertJsonPath('data.is_starred', true);

        // Move to Trash (Soft Delete)
        $trashRes = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/manage/mail/messages/{$message->id}/trash");

        $trashRes->assertOk();

        $this->assertDatabaseHas('sys_mail_messages', [
            'id' => $message->id,
            'folder' => 'trash',
        ]);

        // Restore to Inbox
        $restoreRes = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/manage/mail/messages/{$message->id}/restore");

        $restoreRes->assertOk();

        $this->assertDatabaseHas('sys_mail_messages', [
            'id' => $message->id,
            'folder' => 'inbox',
        ]);

        // Permanently Delete
        $deleteRes = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/manage/mail/messages/{$message->id}");

        $deleteRes->assertOk();

        $this->assertDatabaseMissing('sys_mail_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_admin_can_manage_settings_and_empty_trash(): void
    {
        // Save settings
        $settingsRes = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/mail/settings', [
                'signature' => 'Kind regards, Admin',
                'vacation_enabled' => true,
                'auto_check_interval' => 5,
            ]);

        $settingsRes->assertOk();

        // Get settings
        $getSettingsRes = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/manage/mail/settings');

        $getSettingsRes->assertOk()
            ->assertJsonPath('data.vacation_enabled', true);

        // Create trash message and empty
        MailMessage::create([
            'folder' => 'trash',
            'sender_name' => 'Old Mail',
            'sender_email' => 'old@example.com',
            'recipients' => ['admin@jejakawan.com'],
            'subject' => 'To be emptied',
            'body' => '<p>Dust</p>',
        ]);

        $emptyRes = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/manage/mail/trash/empty');

        $emptyRes->assertOk();
        $this->assertDatabaseMissing('sys_mail_messages', ['subject' => 'To be emptied']);
    }

    public function test_unauthenticated_cannot_access_mail(): void
    {
        $this->getJson('/api/v1/manage/mail/messages')
            ->assertUnauthorized();
    }
}
