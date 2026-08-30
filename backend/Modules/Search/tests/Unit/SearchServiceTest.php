<?php

namespace Modules\Search\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Library\Models\Category;
use Modules\Library\Models\Tag;
use Modules\Publishing\Models\Content;
use Modules\Search\Models\SearchIndex;
use Modules\Search\Models\SearchQuery;
use Modules\Search\Services\SearchService;
use Modules\Search\Tests\SearchTestCase;

class SearchServiceTest extends SearchTestCase
{
    use RefreshDatabase;

    protected SearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SearchService::class);
    }

    public function test_search_strict_and_loose(): void
    {
        // Create indexes directly
        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => 1,
            'title' => 'Exclusive Laravel Tutorial',
            'content' => 'Learn how to build apps',
            'relevance_score' => 100,
        ]);

        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => 2,
            'title' => 'Another Tutorial',
            'content' => 'Some other content',
            'relevance_score' => 50,
        ]);

        // 1. Strict Search (AND) - Both words must be present in titles/pub_contents
        $result = $this->service->search('Exclusive Laravel');
        $this->assertCount(1, $result['results']);
        $this->assertEquals('Exclusive Laravel Tutorial', $result['results'][0]['title']);
        $this->assertFalse($result['is_loose']);

        // 2. Loose Search (OR) - Fallback when strict returns nothing
        $resultLoose = $this->service->search('Exclusive Unknown');
        $this->assertCount(1, $resultLoose['results']);
        $this->assertTrue($resultLoose['is_loose']);
    }

    public function test_search_with_filters(): void
    {
        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => 1,
            'title' => 'News Post',
            'content' => 'Relevant content',
            'type' => 'post',
            'relevance_score' => 10,
        ]);

        SearchIndex::create([
            'searchable_type' => 'Page',
            'searchable_id' => 1,
            'title' => 'News Page',
            'content' => 'Relevant content',
            'type' => 'page',
            'relevance_score' => 10,
        ]);

        $result = $this->service->search('News', ['type' => 'post']);
        $this->assertCount(1, $result['results']);
        $this->assertEquals('post', $result['results'][0]['type']);
    }

    public function test_get_suggestions(): void
    {
        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => 1,
            'title' => 'Laravel Guide',
            'content' => 'Guide content',
            'type' => 'post',
        ]);

        $suggestions = $this->service->getSuggestions('Lara');
        $this->assertCount(1, $suggestions);
        $this->assertEquals('Laravel Guide', $suggestions[0]['text']);
    }

    public function test_reindex_all(): void
    {
        // One content (creates 1 category)
        Content::factory()->create(['title' => 'Post One', 'status' => 'published']);
        // One standalone active category
        Category::factory()->create(['name' => 'Cat One', 'is_active' => true]);
        Tag::factory()->create(['name' => 'Tag One']);

        $counts = $this->service->reindexAll();

        // Verify counts returned by the reindex service
        $this->assertEquals(1, $counts['pub_contents']);
        $this->assertEquals(2, $counts['pub_categories']);
        $this->assertEquals(1, $counts['pub_tags']);

        // Verify that items are successfully written to the database
        $this->assertDatabaseCount('srch_indexes', SearchIndex::count());
        $this->assertGreaterThan(0, SearchIndex::count());
    }

    public function test_search_empty_query(): void
    {
        $result = $this->service->search('');
        $this->assertCount(0, $result['results']);
    }

    public function test_search_by_type(): void
    {
        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => 1,
            'title' => 'Type Test',
            'content' => 'Content here',
            'type' => 'custom',
            'relevance_score' => 10,
        ]);

        $result = $this->service->searchByType('Type', 'custom');
        $this->assertCount(1, $result['results']);
    }

    public function test_search_by_uuid(): void
    {
        $uuid = '019e3699-7fcf-7345-83c2-d81fcd29cc75';

        SearchIndex::create([
            'searchable_type' => 'Post',
            'searchable_id' => $uuid,
            'title' => 'Specific Document Title',
            'content' => 'Some highly specific information that does not mention the uuid string inside.',
            'type' => 'post',
            'relevance_score' => 10,
        ]);

        // Search by UUID exactly
        $result = $this->service->search($uuid);
        $this->assertCount(1, $result['results']);
        $this->assertEquals('Specific Document Title', $result['results'][0]['title']);

        // Search by UUID inside getSuggestions (truncate query log to isolate from search history)
        SearchQuery::truncate();
        $suggestions = $this->service->getSuggestions($uuid);
        $this->assertCount(1, $suggestions);
        $this->assertEquals('Specific Document Title', $suggestions[0]['text']);
    }
}
