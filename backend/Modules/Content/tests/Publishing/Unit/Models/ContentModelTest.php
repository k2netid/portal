<?php

namespace Modules\Content\Publishing\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Library\Models\Category;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class ContentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test content belongs to author.
     */
    public function test_content_belongs_to_author(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['author_id' => $user->id]);

        $this->assertInstanceOf(User::class, $content->author);
        $this->assertEquals($user->id, $content->author->id);
    }

    /**
     * Test content belongs to category.
     */
    public function test_content_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $content = Content::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $content->category);
        $this->assertEquals($category->id, $content->category->id);
    }

    /**
     * Test content has many tags.
     */
    public function test_content_has_many_tags(): void
    {
        $content = Content::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $content->tags()->attach([$tag1->id, $tag2->id]);

        $this->assertCount(2, $content->tags);
        $this->assertTrue($content->tags->contains($tag1));
        $this->assertTrue($content->tags->contains($tag2));
    }

    /**
     * Test content can be published.
     */
    public function test_content_can_be_published(): void
    {
        $content = Content::factory()->create([
            'status' => 'draft',
        ]);

        $content->update(['status' => 'published']);

        $this->assertEquals('published', $content->status);
    }

    /**
     * Test content increments views.
     */
    public function test_content_increments_views(): void
    {
        $content = Content::factory()->create(['views' => 0]);

        $content->increment('views');

        $this->assertEquals(1, $content->fresh()->views);
    }

    /**
     * Test content has soft deletes.
     */
    public function test_content_has_soft_deletes(): void
    {
        $content = Content::factory()->create();
        $contentId = $content->id;

        $content->delete();

        $this->assertSoftDeleted('pub_contents', ['id' => $contentId]);
    }

    /**
     * Test content can be restored.
     */
    public function test_content_can_be_restored(): void
    {
        $content = Content::factory()->create();
        $content->delete();

        $content->restore();

        $this->assertNotSoftDeleted('pub_contents', ['id' => $content->id]);
    }

    /**
     * Test content meta is cast to array.
     */
    public function test_content_meta_is_cast_to_array(): void
    {
        $content = Content::factory()->create([
            'meta' => ['key' => 'value'],
        ]);

        $this->assertIsArray($content->meta);
        $this->assertEquals('value', $content->meta['key']);
    }
}
