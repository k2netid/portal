<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Webhook;
use Tests\TestCase;

class WebhooksAndRedirectsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_manage_webhooks(): void
    {
        $admin = $this->createAdminUser();

        // 1. Create Webhook
        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/webhooks', [
                'name' => 'Slack Alerts',
                'url' => 'https://hooks.slack.com/services/test/123',
                'events' => ['user.created', 'system.backup.completed'],
                'secret' => 'supersecrettoken123',
                'is_active' => true,
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'Slack Alerts');

        $webhookId = (string) $createResponse->json('data.id');

        // 2. List Webhooks
        $listResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/webhooks');

        $listResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'url', 'events'],
                ],
            ]);

        // 3. Trigger Test Webhook
        $triggerResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/webhooks/'.$webhookId.'/trigger', [
                'payload' => ['test' => true, 'timestamp' => time()],
            ]);

        $triggerResponse->assertOk();

        // 4. Delete Webhook
        $deleteResponse = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/manage/infra/webhooks/'.$webhookId);

        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('infra_webhooks', ['id' => $webhookId]);
    }

    public function test_admin_can_manage_infrastructure_redirects(): void
    {
        $admin = $this->createAdminUser();

        // 1. Create Redirect
        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/redirects', [
                'from_domain' => 'old.jejakawan.com',
                'to_domain' => 'portal.jejakawan.com',
                'status_code' => 301,
                'keep_path' => true,
                'is_active' => true,
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.from_domain', 'old.jejakawan.com');

        $redirectId = (string) $createResponse->json('data.id');

        // 2. List Redirects
        $listResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/redirects');

        $listResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'from_domain', 'to_domain', 'status_code'],
                    ],
                ],
            ]);

        // 3. Toggle Redirect
        $toggleResponse = $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/manage/infra/redirects/'.$redirectId.'/toggle');

        $toggleResponse->assertOk()
            ->assertJsonPath('data.is_active', false);

        // 4. Delete Redirect
        $deleteResponse = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/manage/infra/redirects/'.$redirectId);

        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('infra_redirects', ['id' => $redirectId]);
    }

    public function test_unauthenticated_cannot_access_webhooks_or_redirects(): void
    {
        $this->getJson('/api/v1/manage/infra/webhooks')->assertUnauthorized();
        $this->getJson('/api/v1/manage/infra/redirects')->assertUnauthorized();
        $this->postJson('/api/v1/manage/infra/webhooks', [])->assertUnauthorized();
        $this->postJson('/api/v1/manage/infra/redirects', [])->assertUnauthorized();
    }
}
