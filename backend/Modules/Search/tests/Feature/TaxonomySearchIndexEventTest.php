<?php

declare(strict_types=1);

namespace Modules\Search\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Library\Models\Category;
use Modules\Search\Models\SearchIndex;
use Modules\Search\Tests\SearchTestCase;

class TaxonomySearchIndexEventTest extends SearchTestCase
{
    use RefreshDatabase;

    public function test_category_save_indexes_via_taxonomy_event(): void
    {
        $category = Category::create([
            'name' => 'Event Category',
            'slug' => 'event-category',
            'description' => 'Indexed through TaxonomySearchIndexChanged',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->assertDatabaseHas('srch_indexes', [
            'searchable_type' => Category::class,
            'searchable_id' => (string) $category->id,
        ]);

        $category->update(['is_active' => false]);

        $this->assertDatabaseMissing('srch_indexes', [
            'searchable_type' => Category::class,
            'searchable_id' => (string) $category->id,
        ]);
    }

    public function test_category_delete_removes_search_index(): void
    {
        $category = Category::create([
            'name' => 'Delete Me',
            'slug' => 'delete-me-cat',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category->delete();

        $this->assertSame(
            0,
            SearchIndex::query()
                ->where('searchable_type', Category::class)
                ->where('searchable_id', (string) $category->id)
                ->count()
        );
    }
}
