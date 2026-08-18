<?php

declare(strict_types=1);

namespace Modules\Intelligence\Ai\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Support\HubSubscriptionScope;
use Modules\Intelligence\Ai\Models\AiTaxonomyBatch;
use Modules\Intelligence\Ai\Models\AiUsageLog;
use Modules\Intelligence\Ai\Services\PublishingTaxonomySuggestService;
use Modules\Intelligence\Ai\Tests\Unit\FakeTaxonomyAiProvider;
use Tests\TestCase;

require_once __DIR__.'/../Unit/PublishingTaxonomySuggestServiceTest.php';

class AiSubscriptionIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        config(['queue.default' => 'sync']);

        Setting::set('gemini_api_key', 'test-fake-key');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'This is a mocked Gemini response content.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->app->singleton(
            PublishingTaxonomySuggestService::class,
            fn () => new PublishingTaxonomySuggestService(new FakeTaxonomyAiProvider),
        );
    }

    public function test_ai_token_usage_is_isolated_per_subscription(): void
    {
        $admin = $this->createAdminUser();

        // 1. Subscription A setup
        $packageA = $this->createPlatformPackage([
            'id' => 'pack-a',
            'name' => 'Package A',
            'ai_monthly_token_limit' => 10000,
            'features' => ['ai' => true],
        ]);
        $subA = $this->createPlatformSubscription([
            'name' => 'Sub A',
            'domain' => 'sub-a.example.com',
            'license_key' => 'LIC-A',
            'package_id' => $packageA->id,
        ]);

        // 2. Subscription B setup
        $packageB = $this->createPlatformPackage([
            'id' => 'pack-b',
            'name' => 'Package B',
            'ai_monthly_token_limit' => 10000,
            'features' => ['ai' => true],
        ]);
        $subB = $this->createPlatformSubscription([
            'name' => 'Sub B',
            'domain' => 'sub-b.example.com',
            'license_key' => 'LIC-B',
            'package_id' => $packageB->id,
        ]);

        // 3. Log 8000 tokens for Subscription A (limit is 10000)
        AiUsageLog::query()->create([
            'subscription_id' => $subA->id,
            'feature' => 'generate',
            'tokens_in' => 4000,
            'tokens_out' => 4000,
        ]);

        // 4. Test under Subscription B - should be fully successful (using 0 tokens so far)
        HubSubscriptionScope::set($subB->id, $subB->domain);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/ai/generate', [
                'prompt' => 'Hello Subscription B',
            ]);

        // Assert B is NOT blocked (status is 200, not 429)
        $response->assertStatus(200);

        // 5. Log another 3000 tokens for Subscription A (bringing total to 11000)
        AiUsageLog::query()->create([
            'subscription_id' => $subA->id,
            'feature' => 'generate',
            'tokens_in' => 1500,
            'tokens_out' => 1500,
        ]);

        // 6. Test under Subscription A - should return 429 as quota is exceeded
        HubSubscriptionScope::set($subA->id, $subA->domain);

        $responseA = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/ai/generate', [
                'prompt' => 'Hello Subscription A',
            ]);

        $responseA->assertStatus(429);
    }

    public function test_taxonomy_batches_are_isolated_per_subscription(): void
    {
        $admin = $this->createAdminUser();

        // Sub A setup
        $subA = $this->createPlatformSubscription([
            'name' => 'Sub A',
            'domain' => 'sub-a.example.com',
            'license_key' => 'LIC-A',
        ]);

        // Sub B setup
        $subB = $this->createPlatformSubscription([
            'name' => 'Sub B',
            'domain' => 'sub-b.example.com',
            'license_key' => 'LIC-B',
        ]);

        // Create a batch under Sub A
        HubSubscriptionScope::set($subA->id, $subA->domain);
        $batchResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/ai/taxonomy-batches', [
                'items' => [
                    [
                        'ref' => 'post-1',
                        'title' => 'Title under Sub A',
                    ],
                ],
            ]);

        $batchResponse->assertStatus(202);
        $batchId = $batchResponse->json('data.id');

        // Switch to Sub B
        HubSubscriptionScope::set($subB->id, $subB->domain);

        // Sub B lists batches - should see 0 batches (fully isolated!)
        $indexB = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/ai/taxonomy-batches');
        $indexB->assertOk()
            ->assertJsonCount(0, 'data');

        // Sub B tries to access Sub A's batch by ID - should return 404 (prevent IDOR!)
        $showB = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/ai/taxonomy-batches/'.$batchId);
        $showB->assertStatus(404);
    }

    public function test_taxonomy_batch_saves_user_uuid_consistently(): void
    {
        $admin = $this->createAdminUser();

        $sub = $this->createPlatformSubscription([
            'name' => 'Sub A',
            'domain' => 'sub-a.example.com',
            'license_key' => 'LIC-A',
        ]);

        HubSubscriptionScope::set($sub->id, $sub->domain);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/ai/taxonomy-batches', [
                'items' => [
                    [
                        'ref' => 'post-1',
                        'title' => 'UUID check',
                    ],
                ],
            ]);

        $response->assertStatus(202);
        $batchId = $response->json('data.id');

        // Retrieve from DB and assert user_id column holds the admin's UUID string
        $batch = AiTaxonomyBatch::query()->find($batchId);
        $this->assertNotNull($batch);
        $this->assertEquals($admin->id, $batch->user_id);
    }
}
