<?php

declare(strict_types=1);

namespace Modules\Publishing\Tests\Feature;

use Modules\Publishing\Models\Content;
use Tests\TestCase;

class PublishingContentGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_manage_contents_forbidden_when_pack_inactive(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/publishing/contents')
            ->assertForbidden();
    }

    public function test_public_contents_lists_published_when_pack_active(): void
    {
        $this->activatePack('publishing');
        $admin = $this->createAdminUser();
        $content = Content::factory()->published()->create([
            'author_id' => $admin->id,
            'category_id' => null,
            'type' => 'post',
            'title' => 'Published pack post',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/publishing/contents')
            ->assertOk();

        $this->getJson('/api/v1/public/publishing/contents')
            ->assertOk()
            ->assertJsonFragment(['slug' => $content->slug]);
    }

    public function test_manage_contents_filters_by_category_and_uncategorized(): void
    {
        $this->activatePack('publishing');
        $admin = $this->createAdminUser();

        $category = \Modules\Library\Models\Category::create([
            'name' => 'Prestasi Siswa',
            'slug' => 'prestasi-siswa',
            'is_active' => true,
        ]);

        $categorizedContent = Content::factory()->published()->create([
            'author_id' => $admin->id,
            'category_id' => $category->id,
            'type' => 'post',
            'title' => 'Categorized Post',
        ]);

        $uncategorizedContent = Content::factory()->published()->create([
            'author_id' => $admin->id,
            'category_id' => null,
            'type' => 'post',
            'title' => 'Uncategorized Post',
        ]);

        // 1. Filter by category_id (UUID)
        $resId = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/manage/publishing/contents?category_id={$category->id}")
            ->assertOk()
            ->json('data.data');

        $this->assertTrue(collect($resId)->contains('id', $categorizedContent->id));
        $this->assertFalse(collect($resId)->contains('id', $uncategorizedContent->id));
        $firstItem = collect($resId)->firstWhere('id', $categorizedContent->id);
        $this->assertNotNull($firstItem['category'] ?? null);
        $this->assertEquals('Prestasi Siswa', $firstItem['category']['name']);

        // 2. Filter by category (slug)
        $resSlug = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/publishing/contents?category=prestasi-siswa')
            ->assertOk()
            ->json('data.data');

        $this->assertTrue(collect($resSlug)->contains('id', $categorizedContent->id));
        $this->assertFalse(collect($resSlug)->contains('id', $uncategorizedContent->id));

        // 3. Filter by uncategorized
        $resUncat = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/publishing/contents?category_id=uncategorized')
            ->assertOk()
            ->json('data.data');

        $this->assertTrue(collect($resUncat)->contains('id', $uncategorizedContent->id));
        $this->assertFalse(collect($resUncat)->contains('id', $categorizedContent->id));

        // 4. Filter by 'all' should return both
        $resAll = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/publishing/contents?category_id=all')
            ->assertOk()
            ->json('data.data');

        $this->assertTrue(collect($resAll)->contains('id', $categorizedContent->id));
        $this->assertTrue(collect($resAll)->contains('id', $uncategorizedContent->id));
    }
}
