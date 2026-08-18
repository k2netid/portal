<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Modules\Core\System\Jobs\ProcessOutboundWebhook;
use Tests\TestCase;

class IamEvolutionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_kyc_step_updates_user_profile(): void
    {
        $admin = $this->createAdminUser([
            'onboarding_step' => 0,
            'kyc_level' => 'level_0',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/profile/kyc/step', [
                'step' => 1,
                'level' => 'level_1',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.onboarding_step', 1)
            ->assertJsonPath('data.kyc_level', 'level_1');

        $admin->refresh();
        $this->assertSame(1, (int) $admin->onboarding_step);
        $this->assertSame('level_1', $admin->kyc_level);
    }

    public function test_active_sessions_lists_sanctum_tokens(): void
    {
        $admin = $this->createAdminUser();
        $token = $admin->createToken('test-device');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/manage/system/profile/sessions');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $sessions = $response->json('data');
        $this->assertIsArray($sessions);
        $this->assertNotEmpty($sessions);
    }

    public function test_webhook_crud_and_test_trigger_dispatches_job(): void
    {
        Queue::fake();
        $admin = $this->createAdminUser();

        $create = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/webhooks', [
                'name' => 'IAM Test Hook',
                'url' => 'https://example.com/hooks/iam',
                'events' => ['user.updated'],
                'is_active' => true,
            ]);

        $create->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'IAM Test Hook');

        $webhookId = $create->json('data.id');
        $this->assertNotEmpty($webhookId);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/webhooks')
            ->assertOk()
            ->assertJsonFragment(['name' => 'IAM Test Hook']);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/webhooks/{$webhookId}/trigger", [
                'payload' => ['ping' => true],
            ])
            ->assertOk();

        Queue::assertPushed(ProcessOutboundWebhook::class);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/manage/infra/webhooks/{$webhookId}")
            ->assertOk();

        $this->assertDatabaseMissing('infra_webhooks', ['id' => $webhookId]);
    }

    public function test_abac_policy_crud_requires_security_permission(): void
    {
        $admin = $this->createAdminUser(['kyc_level' => 'level_1']);

        $create = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/security/abac-policies', [
                'name' => 'Require KYC L2',
                'description' => 'Test policy',
                'target_resource' => 'financial_reports',
                'action' => 'view',
                'conditions' => [
                    ['attribute' => 'user.kyc_level', 'operator' => '>=', 'value' => 'level_2'],
                ],
                'is_active' => true,
            ]);

        $create->assertCreated()
            ->assertJsonPath('data.name', 'Require KYC L2');

        $policyId = $create->json('data.id');

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/security/abac-policies')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Require KYC L2']);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/manage/security/abac-policies/{$policyId}")
            ->assertOk();

        $this->assertDatabaseMissing('sys_abac_policies', ['id' => $policyId]);
    }

    public function test_abac_store_blocked_without_kyc(): void
    {
        $admin = $this->createAdminUser(['kyc_level' => 'level_0']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/security/abac-policies', [
                'name' => 'Blocked',
                'target_resource' => '*',
                'action' => '*',
                'conditions' => [],
                'is_active' => true,
            ])
            ->assertStatus(403)
            ->assertJsonPath('required_level', 'level_1');
    }

    public function test_webhook_deliveries_endpoint_returns_list(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/webhooks/deliveries/recent')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_siem_exports_endpoint(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/security/siem/exports')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_scim_users_index_rejects_invalid_bearer_when_token_configured(): void
    {
        Config::set('services.scim.token', 'test-scim-secret');
        $this->assertSame('test-scim-secret', config('services.scim.token'));

        $bad = $this->withHeaders(['Authorization' => 'Bearer wrong-token'])
            ->getJson('/api/scim/v2/Users');
        $bad->assertStatus(401);

        $this->withHeaders(['Authorization' => 'Bearer test-scim-secret'])
            ->getJson('/api/scim/v2/Users')
            ->assertOk()
            ->assertJsonStructure([
                'schemas',
                'totalResults',
                'Resources',
            ]);
    }
}
