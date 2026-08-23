<?php

namespace Modules\Search\Tests\Feature;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Publishing\Dto\SearchableContentSnapshot;
use Modules\Publishing\Models\Content;
use Modules\Search\Contracts\SearchIndexerInterface;
use Modules\Search\Models\SearchIndex;
use Modules\Search\Tests\SearchTestCase;
use Tests\Helpers\TestHelpers;

class SearchFunctionalityTest extends SearchTestCase
{
    use RefreshDatabase;

    /**
     * Test user can search for content.
     */
    public function test_user_can_search_for_content(): void
    {
        // Create content with search index
        $content = Content::factory()->published()->create([
            'title' => 'Test Article About Laravel',
            'body' => 'This is a test article about Laravel framework.',
        ]);

        // Create search index entry
        SearchIndex::create([
            'searchable_type' => $content::class,
            'searchable_id' => $content->id,
            'title' => $content->title,
            'content' => $content->body,
            'url' => '/content/'.$content->slug,
            'type' => 'content',
            'relevance_score' => 1.0,
        ]);

        $response = $this->getJson('/api/v1/public/search?q=laravel');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'results',
                'total',
                'query',
            ],
        ]);
    }

    /**
     * Test search returns empty for no results.
     */
    public function test_search_returns_empty_for_no_results(): void
    {
        $response = $this->getJson('/api/v1/public/search?q=nonexistentterm12345');

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'results' => [],
                'total' => 0,
            ],
        ]);
    }

    /**
     * Test search can filter by type.
     */
    public function test_search_can_filter_by_type(): void
    {
        // Create different types of content
        $uniqueTitle = 'UniqueTestContent'.uniqid();
        $content = Content::factory()->published()->create([
            'title' => $uniqueTitle,
            'type' => 'post',
        ]);
        $snapshot = SearchableContentSnapshot::fromContent($content);
        app(SearchIndexerInterface::class)->syncPublishing($snapshot);

        $response = $this->getJson('/api/v1/public/search?q='.urlencode($uniqueTitle).'&type=post');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonCount(1, 'data.results');
    }

    /**
     * Test search suggestions endpoint.
     */
    public function test_search_suggestions_endpoint(): void
    {
        $content = Content::factory()->published()->create([
            'title' => 'Laravel Framework Guide',
            'type' => 'post',
        ]);

        SearchIndex::create([
            'searchable_type' => $content::class,
            'searchable_id' => $content->id,
            'title' => $content->title,
            'content' => $content->body,
            'url' => '/content/'.$content->slug,
            'type' => 'post',
            'relevance_score' => 1.0,
        ]);

        $response = $this->getJson('/api/v1/public/search/suggestions?q=laravel');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
    }

    /**
     * Test search is rate limited.
     */
    public function test_search_is_rate_limited(): void
    {
        // Production limiter is generous (burst + 10‑min window); override for a fast, deterministic check.
        RateLimiter::for('search-public', fn (Request $request) => Limit::perMinute(5)->by('search-public-test|'.$request->ip()));

        for ($i = 0; $i < 6; $i++) {
            $response = $this->getJson('/api/v1/public/search?q=test');
        }

        $response->assertStatus(429);
    }

    /**
     * Test search handles empty query.
     */
    public function test_search_handles_empty_query(): void
    {
        $response = $this->getJson('/api/v1/public/search?q=');

        // Validation fails if required
        $response->assertStatus(422);
    }

    /**
     * Test search results are ordered by relevance.
     */
    public function test_search_results_ordered_by_relevance(): void
    {
        $content1 = Content::factory()->published()->create(['title' => 'Laravel Tutorial', 'type' => 'post']);
        $content2 = Content::factory()->published()->create(['title' => 'Laravel Advanced', 'type' => 'post']);

        SearchIndex::create([
            'searchable_type' => $content1::class,
            'searchable_id' => $content1->id,
            'title' => $content1->title,
            'content' => $content1->body,
            'url' => '/content/'.$content1->slug,
            'type' => 'post',
            'relevance_score' => 0.8,
        ]);

        SearchIndex::create([
            'searchable_type' => $content2::class,
            'searchable_id' => $content2->id,
            'title' => $content2->title,
            'content' => $content2->body,
            'url' => '/content/'.$content2->slug,
            'type' => 'post',
            'relevance_score' => 0.9,
        ]);

        $response = $this->getJson('/api/v1/public/search?q=laravel');

        TestHelpers::assertApiSuccess($response);
        $results = $response->json('data.results');

        // Results should be ordered by relevance (descending)
        if (count($results) >= 2) {
            $this->assertGreaterThanOrEqual(
                $results[1]['relevance_score'] ?? 0,
                $results[0]['relevance_score'] ?? 0
            );
        }
    }
}
