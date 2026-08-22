<?php

declare(strict_types=1);

namespace Modules\Mail\Tests\Feature;

use Modules\Core\System\Models\User;
use Modules\Mail\Models\MailAccount;
use Modules\Mail\Tests\Support\ActivatesMailExtension;
use Tests\TestCase;

class MailAccountControllerTest extends TestCase
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

    public function test_user_can_list_mail_accounts_and_auto_initialize_default(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/manage/mail/accounts');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'accounts',
                    'capabilities',
                ],
            ]);

        $this->assertDatabaseHas('sys_mail_accounts', [
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
    }

    public function test_user_can_connect_custom_personal_account(): void
    {
        $this->user->update(['is_super_admin' => true]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/mail/accounts', [
                'name' => 'Secondary Desk',
                'email' => 'secondary@domain.com',
                'account_type' => 'custom_personal',
                'smtp_host' => 'smtp.domain.com',
                'smtp_port' => 587,
                'smtp_username' => 'secondary@domain.com',
                'smtp_password' => 'secret123',
                'smtp_encryption' => 'tls',
                'imap_host' => 'imap.domain.com',
                'imap_port' => 993,
                'imap_username' => 'secondary@domain.com',
                'imap_password' => 'secret123',
                'imap_encryption' => 'ssl',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Secondary Desk')
            ->assertJsonPath('data.email', 'secondary@domain.com');

        $this->assertDatabaseHas('sys_mail_accounts', [
            'user_id' => $this->user->id,
            'name' => 'Secondary Desk',
        ]);
    }

    public function test_user_can_update_account(): void
    {
        $account = MailAccount::create([
            'user_id' => $this->user->id,
            'name' => 'Old Desk',
            'email' => 'desk@domain.com',
            'account_type' => 'system_global',
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/manage/mail/accounts/{$account->id}", [
                'name' => 'Updated Desk',
                'signature' => 'Best regards, Agent',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Desk')
            ->assertJsonPath('data.signature', 'Best regards, Agent');

        $this->assertDatabaseHas('sys_mail_accounts', [
            'id' => $account->id,
            'name' => 'Updated Desk',
        ]);
    }

    public function test_user_can_set_default_account(): void
    {
        $acc1 = MailAccount::create([
            'user_id' => $this->user->id,
            'name' => 'Acc 1',
            'email' => 'acc1@domain.com',
            'is_default' => true,
        ]);

        $acc2 = MailAccount::create([
            'user_id' => $this->user->id,
            'name' => 'Acc 2',
            'email' => 'acc2@domain.com',
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/manage/mail/accounts/{$acc2->id}/default");

        $response->assertOk()
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas('sys_mail_accounts', ['id' => $acc1->id, 'is_default' => false]);
        $this->assertDatabaseHas('sys_mail_accounts', ['id' => $acc2->id, 'is_default' => true]);
    }

    public function test_user_can_delete_account_and_reassign_default(): void
    {
        $acc1 = MailAccount::create([
            'user_id' => $this->user->id,
            'name' => 'Acc 1',
            'email' => 'acc1@domain.com',
            'is_default' => true,
        ]);

        $acc2 = MailAccount::create([
            'user_id' => $this->user->id,
            'name' => 'Acc 2',
            'email' => 'acc2@domain.com',
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/manage/mail/accounts/{$acc1->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('sys_mail_accounts', ['id' => $acc1->id]);
        $this->assertDatabaseHas('sys_mail_accounts', ['id' => $acc2->id, 'is_default' => true]);
    }

    public function test_user_can_test_connection_handshake(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/manage/mail/accounts/test', [
                'host' => '127.0.0.1',
                'port' => 8000,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['host']);
    }

    public function test_unauthenticated_cannot_access_accounts(): void
    {
        $this->getJson('/api/v1/manage/mail/accounts')
            ->assertUnauthorized();
    }
}
