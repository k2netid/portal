<?php

declare(strict_types=1);

namespace Modules\CmsAi\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CmsAi\Services\PublishingTaxonomySuggestService;
use Modules\CmsAi\Tests\CmsAiTestCase;
use Modules\CmsAi\Tests\Unit\FakeTaxonomyAiProvider;

require_once __DIR__.'/../Unit/PublishingTaxonomySuggestServiceTest.php';

class AiTaxonomyBatchTest extends CmsAiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['queue.default' => 'sync']);
        $this->app->singleton(
            PublishingTaxonomySuggestService::class,
            fn () => new PublishingTaxonomySuggestService(new FakeTaxonomyAiProvider),
        );
    }

    public function test_taxonomy_batch_processes_items_on_sync_queue(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/ai/taxonomy-batches', [
                'items' => [
                    [
                        'ref' => 'post-1',
                        'title' => 'Semester opening',
                        'excerpt' => 'Welcome students',
                        'existing_categories' => ['News'],
                    ],
                    [
                        'ref' => 'post-2',
                        'title' => 'Library hours',
                    ],
                ],
            ]);

        $response->assertStatus(202)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.total_items', 2)
            ->assertJsonPath('data.completed_items', 2)
            ->assertJsonPath('data.failed_items', 0);

        $batchId = $response->json('data.id');
        $this->assertIsString($batchId);

        $show = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/ai/taxonomy-batches/'.$batchId);

        $show->assertOk()
            ->assertJsonPath('data.results.0.ref', 'post-1')
            ->assertJsonPath('data.results.0.result.category_name', 'Academics');

        $index = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/ai/taxonomy-batches');

        $index->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertDatabaseHas('ai_taxonomy_batches', [
            'id' => $batchId,
            'status' => 'completed',
            'total_items' => 2,
        ]);
    }

    public function test_taxonomy_batch_rejects_empty_items(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/ai/taxonomy-batches', ['items' => []])
            ->assertStatus(422);
    }

    public function test_taxonomy_batch_show_returns_404_for_unknown_id(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/ai/taxonomy-batches/00000000-0000-0000-0000-000000000000')
            ->assertStatus(404);
    }
}
