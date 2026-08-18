<?php

declare(strict_types=1);

namespace Modules\Intelligence\Ai\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Support\HubSubscriptionScope;
use Modules\Intelligence\Ai\Models\AiUsageLog;
use Tests\TestCase;

class AiSubscriptionQuotaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_ai_generate_returns_429_when_subscription_quota_exceeded(): void
    {
        $admin = $this->createAdminUser();

        $package = $this->createPlatformPackage([
            'id' => 'quota-test',
            'name' => 'Quota Test',
            'ai_monthly_token_limit' => 100,
            'features' => ['ai' => true],
        ]);

        $subscription = $this->createPlatformSubscription([
            'name' => 'Quota Subscription',
            'domain' => 'quota-subscription.example.com',
            'license_key' => 'LIC-QUOTA-TEST',
            'package_id' => $package->id,
        ]);

        HubSubscriptionScope::set($subscription->id, $subscription->domain);

        AiUsageLog::query()->create([
            'subscription_id' => $subscription->id,
            'feature' => 'generate',
            'tokens_in' => 50,
            'tokens_out' => 60,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/ai/generate', [
                'prompt' => 'Write a long article about testing quotas in platform products.',
            ]);

        $response->assertStatus(429);
    }
}
