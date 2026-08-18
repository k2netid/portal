<?php

declare(strict_types=1);

namespace Modules\Intelligence\Search\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Library\Contracts\TaxonomySearchPortInterface;
use Modules\Content\Library\Models\Category;
use Modules\Content\Publishing\Contracts\PublishingSearchReadPortInterface;
use Modules\Content\Publishing\Dto\SearchableContentSnapshot;
use Modules\Content\Publishing\Events\ContentDeleted;
use Modules\Content\Publishing\Events\ContentPublished;
use Modules\Content\Publishing\Events\ContentUnpublished;
use Modules\Content\Publishing\Models\Content;
use Modules\Intelligence\Search\Contracts\SearchIndexerInterface;
use Modules\Intelligence\Search\Listeners\SyncContentSearchIndex;
use Modules\Intelligence\Search\Models\SearchIndex;
use Tests\TestCase;

class UnifiedSearchIndexerTest extends TestCase
{
    use RefreshDatabase;

    protected SearchIndexerInterface $indexer;

    protected PublishingSearchReadPortInterface $publishingRead;

    protected TaxonomySearchPortInterface $taxonomyPort;

    protected function setUp(): void
    {
        parent::setUp();
        $this->indexer = $this->app->make(SearchIndexerInterface::class);
        $this->publishingRead = $this->app->make(PublishingSearchReadPortInterface::class);
        $this->taxonomyPort = $this->app->make(TaxonomySearchPortInterface::class);
    }

    public function test_sync_published_content_creates_index_row(): void
    {
        $content = Content::factory()->create([
            'status' => 'published',
            'title' => 'Unified Index Article',
            'slug' => 'unified-index-article',
            'type' => 'post',
        ]);

        $snapshot = SearchableContentSnapshot::fromContent($content);
        $this->indexer->syncPublishing($snapshot);

        $this->assertDatabaseHas('srch_indexes', [
            'searchable_type' => Content::class,
            'searchable_id' => $content->id,
            'title' => 'Unified Index Article',
            'type' => 'post',
        ]);
    }

    public function test_sync_draft_content_removes_index_row(): void
    {
        $content = Content::factory()->create([
            'status' => 'published',
            'slug' => 'draft-removal-test',
        ]);
        $this->indexer->syncPublishing(SearchableContentSnapshot::fromContent($content));

        $content->update(['status' => 'draft']);
        $draftSnapshot = SearchableContentSnapshot::fromContent($content->fresh());
        $this->indexer->syncPublishing($draftSnapshot);

        $this->assertDatabaseMissing('srch_indexes', [
            'searchable_type' => Content::class,
            'searchable_id' => $content->id,
        ]);
    }

    public function test_content_published_event_indexes_via_listener(): void
    {
        $content = Content::factory()->create([
            'status' => 'published',
            'title' => 'Event Driven Index',
            'slug' => 'event-driven-index',
        ]);

        $listener = $this->app->make(SyncContentSearchIndex::class);
        $listener->handlePublished(new ContentPublished($content));

        $this->assertDatabaseHas('srch_indexes', [
            'searchable_id' => $content->id,
            'title' => 'Event Driven Index',
        ]);
    }

    public function test_content_unpublished_event_removes_index(): void
    {
        $content = Content::factory()->create(['status' => 'published', 'slug' => 'unpub-test']);
        SearchIndex::index($content, ['title' => $content->title, 'type' => 'post']);

        $listener = $this->app->make(SyncContentSearchIndex::class);
        $listener->handleUnpublished(new ContentUnpublished($content));

        $this->assertDatabaseMissing('srch_indexes', [
            'searchable_type' => Content::class,
            'searchable_id' => $content->id,
        ]);
    }

    public function test_content_deleted_event_removes_by_id(): void
    {
        $content = Content::factory()->create(['status' => 'published', 'slug' => 'deleted-test']);
        SearchIndex::index($content, ['title' => $content->title, 'type' => 'post']);
        $contentId = (string) $content->id;
        $content->delete();

        $listener = $this->app->make(SyncContentSearchIndex::class);
        $listener->handleDeleted(new ContentDeleted($contentId));

        $this->assertDatabaseMissing('srch_indexes', [
            'searchable_type' => Content::class,
            'searchable_id' => $contentId,
        ]);
    }

    public function test_sync_inactive_category_removes_index(): void
    {
        $category = Category::factory()->create([
            'is_active' => true,
            'name' => 'Active Cat',
            'slug' => 'active-cat',
        ]);
        $snapshot = $this->taxonomyPort->snapshotForIndex('category', (string) $category->id);
        $this->assertNotNull($snapshot);
        $this->indexer->syncTaxonomy($snapshot);

        $category->update(['is_active' => false]);
        $inactive = $this->taxonomyPort->snapshotForIndex('category', (string) $category->id);
        $this->assertNotNull($inactive);
        $this->indexer->syncTaxonomy($inactive);

        $this->assertDatabaseMissing('srch_indexes', [
            'searchable_type' => Category::class,
            'searchable_id' => $category->id,
        ]);
    }
}
