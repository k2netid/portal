<?php

namespace Modules\Content\Publishing\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Content\Library\Models\Category;
use Modules\Content\Library\Models\CustomField;
use Modules\Content\Library\Models\FieldGroup;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Publishing\Models\Content;
use Modules\Content\Publishing\Services\ContentService;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class ContentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ContentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new ContentService;
    }

    public function test_get_published_pub_contents(): void
    {
        Content::factory()->count(3)->state(['status' => 'published'])->create();
        Content::factory()->state(['status' => 'draft'])->create();

        $request = new Request;
        $result = $this->service->getPublishedContents($request);

        $this->assertCount(3, $result['data']);
    }

    public function test_get_related_content(): void
    {
        $tag = Tag::factory()->create();
        $content1 = Content::factory()->state(['status' => 'published'])->create();
        $content2 = Content::factory()->state(['status' => 'published'])->create();

        $content1->tags()->attach($tag);
        $content2->tags()->attach($tag);

        $related = $this->service->getRelatedContent($content1->slug);

        $this->assertCount(1, $related);
        $this->assertEquals($content2->id, $related[0]['id']);
    }

    public function test_create_content(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $data = [
            'title' => 'New Post',
            'type' => 'post',
            'status' => 'published',
            'category_id' => $category->id,
            'body' => 'Content body',
        ];

        $content = $this->service->create($data, $user->id, true);

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('New Post', $content->title);
        $this->assertCount(1, $content->revisions);
    }

    public function test_update_content(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        $data = ['title' => 'Updated Title'];
        $updated = $this->service->update($content, $data, $user->id, true);

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertCount(1, $updated->revisions);
    }

    public function test_toggle_featured(): void
    {
        $content = Content::factory()->create(['is_featured' => false]);

        $status = $this->service->toggleFeatured($content);
        $this->assertTrue($status);
        $this->assertTrue($content->fresh()->is_featured);
    }

    public function test_bulk_action_publish(): void
    {
        $c1 = Content::factory()->create(['status' => 'draft']);
        $c2 = Content::factory()->create(['status' => 'draft']);

        $count = $this->service->bulkAction('publish', [$c1->id, $c2->id]);

        $this->assertEquals(2, $count);
        $this->assertEquals('published', $c1->fresh()->status);
    }

    public function test_duplicate_content(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['title' => 'Original']);

        $duplicate = $this->service->duplicate($content, $user->id);

        $this->assertStringContainsString('Original', $duplicate->title);
        $this->assertEquals('draft', $duplicate->status);
    }

    public function test_lock_and_unlock(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create();

        $this->service->lock($content, $user->id);
        $this->assertEquals($user->id, $content->fresh()->locked_by);

        $this->assertTrue($this->service->isLockedByOther($content, 'other-user-id'));

        $this->service->unlock($content);
        $this->assertNull($content->fresh()->locked_by);
    }

    public function test_restore_and_force_delete(): void
    {
        $content = Content::factory()->create();
        $id = $content->id;

        $this->service->delete($content);
        $this->assertSoftDeleted('pub_contents', ['id' => $id]);

        $this->service->restore($id);
        $this->assertFalse($content->fresh()->trashed());

        $this->service->forceDelete($id);
        $this->assertDatabaseMissing('pub_contents', ['id' => $id]);
    }

    public function test_apply_filters_and_sorting(): void
    {
        $category = Category::factory()->create(['slug' => 'news']);
        Content::factory()->create(['category_id' => $category->id, 'status' => 'published', 'type' => 'post', 'is_featured' => true, 'title' => 'A Post']);
        Content::factory()->create(['status' => 'draft', 'title' => 'B Draft']);

        $request = new Request([
            'type' => 'post',
            'is_featured' => 'true',
            'category' => 'news',
            'sort' => 'title',
        ]);

        $result = $this->service->getPublishedContents($request);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('A Post', $result['data'][0]->title);

        $requestAsc = new Request(['sort' => 'title']);
        $resultAsc = $this->service->getPublishedContents($requestAsc);
        $this->assertEquals('A Post', $resultAsc['data'][0]->title);
    }

    public function test_create_with_tags_and_custom_fields(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        // Manual creation since no factory
        FieldGroup::create([
            'name' => 'SEO',
        ]);
        CustomField::create([
            'name' => 'SEO Title',
            'key' => 'seo_title',
            'label' => 'SEO Title',
            'type' => 'text',
        ]);

        $data = [
            'title' => 'Tag Test',
            'type' => 'post',
            'status' => 'published',
            'tags' => [$tag->id],
            'new_tags' => ['Fresh Tag'],
            'custom_fields' => ['seo_title' => 'Best SEO Title'],
            'published_at' => now()->subDay()->timestamp,
            'body' => 'Test body',
        ];

        $content = $this->service->create($data, $user->id, true);

        $this->assertCount(2, $content->tags);
        $this->assertTrue($content->tags->contains('slug', 'fresh-tag'));
        $this->assertEquals('Best SEO Title', $content->getCustomFieldValue('seo_title'));
    }

    public function test_bulk_actions_all(): void
    {
        $c1 = Content::factory()->create(['status' => 'draft']);
        $c2 = Content::factory()->create(['status' => 'pending']);
        $c3 = Content::factory()->create(['status' => 'published']);

        $ids = [$c1->id, $c2->id, $c3->id];

        $this->service->bulkAction('publish', $ids);
        $this->assertEquals('published', $c1->fresh()->status);

        $this->service->bulkAction('reject', [$c2->id]);
        $this->assertEquals('draft', $c2->fresh()->status);

        $this->service->bulkAction('archive', $ids);
        $this->assertEquals('archived', $c1->fresh()->status);

        $this->service->bulkAction('delete', $ids);
        $this->assertCount(3, Content::onlyTrashed()->get());

        $this->service->bulkAction('restore', $ids);
        $this->assertCount(0, Content::onlyTrashed()->get());

        $this->service->bulkAction('force_delete', $ids);
        $this->assertDatabaseMissing('pub_contents', ['id' => $c1->id]);
    }

    public function test_generate_unique_slug_collision(): void
    {
        Content::factory()->create(['slug' => 'test']);
        Content::factory()->create(['slug' => 'test-1']);

        $slug = $this->service->generateUniqueSlug('test');
        $this->assertEquals('test-2', $slug);
    }

    public function test_get_published_pub_contents_with_pagination(): void
    {
        Content::factory()->count(15)->state(['status' => 'published'])->create();

        $request = new Request(['per_page' => 5]);
        $result = $this->service->getPublishedContents($request);

        $this->assertTrue($result['paginated']);
        $this->assertEquals(5, $result['data']->perPage());
    }

    public function test_get_published_pub_contents_with_limit(): void
    {
        Content::factory()->count(10)->state(['status' => 'published'])->create();

        $request = new Request(['limit' => 3]);
        $result = $this->service->getPublishedContents($request);

        $this->assertFalse($result['paginated']);
        $this->assertCount(3, $result['data']);
    }

    public function test_get_related_content_with_category_fallback(): void
    {
        $category = Category::factory()->create();
        $content = Content::factory()->create(['category_id' => $category->id, 'status' => 'published']);

        // Content with same category but no shared tags
        Content::factory()->count(2)->create([
            'category_id' => $category->id,
            'status' => 'published',
        ]);

        $results = $this->service->getRelatedContent($content->slug, 5);
        $this->assertCount(2, $results);
    }

    public function test_empty_trash(): void
    {
        Content::factory()->count(3)->create(['deleted_at' => now()]);
        $count = $this->service->emptyTrash();
        $this->assertEquals(3, $count);
        $this->assertEquals(0, Content::onlyTrashed()->count());
    }

    public function test_bulk_action_change_category(): void
    {
        $category = Category::factory()->create();
        $content = Content::factory()->create();

        $this->service->bulkAction('change_category', [$content->id], $category->id);
        $this->assertEquals($category->id, $content->fresh()->category_id);
    }

    public function test_update_with_comment_status_bool(): void
    {
        $content = Content::factory()->create(['comment_status' => 'closed']);
        $user = User::factory()->create();

        $this->service->update($content, ['comment_status' => true], $user->id);
        $this->assertEquals('open', $content->fresh()->comment_status);

        $this->service->update($content, ['comment_status' => false], $user->id);
        $this->assertEquals('closed', $content->fresh()->comment_status);
    }

    public function test_create_with_string_published_at(): void
    {
        $user = User::factory()->create();
        $data = [
            'title' => 'String Date',
            'type' => 'post',
            'status' => 'published',
            'published_at' => '2025-01-01 10:00:00',
        ];

        $content = $this->service->create($data, $user->id);
        $this->assertEquals('2025-01-01 10:00:00', $content->published_at->toDateTimeString());
    }

    public function test_track_media_usage_untrack(): void
    {
        $content = Content::factory()->create(['featured_image' => 123]);
        $user = User::factory()->create();

        // Update with null image should untrack
        $this->service->update($content, ['featured_image' => null], $user->id);
        $this->assertDatabaseMissing('srv_media_usages', [
            'model_type' => $content::class,
            'model_id' => $content->id,
            'field_name' => 'featured_image',
        ]);
    }

    public function test_bulk_action_reject_and_draft(): void
    {
        $content = Content::factory()->create(['status' => 'published']);
        $this->service->bulkAction('reject', [$content->id]);
        $this->assertEquals('draft', $content->fresh()->status);

        $content->update(['status' => 'published']);
        $this->service->bulkAction('draft', [$content->id]);
        $this->assertEquals('draft', $content->fresh()->status);
    }

    public function test_get_published_pub_contents_complex_filters(): void
    {
        $category = Category::factory()->create(['slug' => 'cat']);
        $tag = Tag::factory()->create(['slug' => 'tag']);
        Content::factory()->create([
            'status' => 'published',
            'category_id' => $category->id,
            'is_featured' => true,
            'type' => 'post',
        ])->tags()->attach($tag);

        $request = new Request([
            'type' => 'post',
            'is_featured' => '1',
            'category' => 'cat',
            'tag' => 'tag',
            'status' => 'published',
            'category_id' => $category->id,
        ]);

        $result = $this->service->getPublishedContents($request);
        $this->assertCount(1, $result['data']);
    }

    public function test_create_revision_on_update(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['title' => 'Old']);

        $updated = $this->service->update($content, ['title' => 'New'], $user->id, true, 'Reason');

        $this->assertCount(1, $updated->revisions);
        $this->assertEquals('Reason', $updated->revisions->first()->reason);
    }
}
