<?php

namespace Modules\Content\Library\Tests\Feature;

use Modules\Content\Library\Models\Tag;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class TagTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    /**
     * Test admin can list all tags.
     */
    public function test_admin_can_list_all_tags(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Tag::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/manage/library/tags');

        TestHelpers::assertApiSuccess($response);
        $this->assertIsArray($response->json('data'));
    }

    /**
     * Test admin can get tag statistics.
     */
    public function test_admin_can_get_tag_statistics(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Tag::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/manage/library/tags/statistics');

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test admin can create tag.
     */
    public function test_admin_can_create_tag(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tagData = [
            'name' => 'Test Tag '.uniqid(),
            'slug' => 'test-tag-'.uniqid(),
            'description' => 'Test tag description',
        ];

        $response = $this->postJson('/api/v1/manage/library/tags', $tagData);

        TestHelpers::assertApiSuccess($response, 201);
        $this->assertDatabaseHas('lib_tags', [
            'name' => $tagData['name'],
            'slug' => $tagData['slug'],
        ]);
    }

    /**
     * Test tag creation requires name.
     */
    public function test_tag_creation_requires_name(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/v1/manage/library/tags', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * Test tag name must be unique.
     */
    public function test_tag_name_must_be_unique(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $existing = Tag::factory()->create();

        $response = $this->postJson('/api/v1/manage/library/tags', [
            'name' => $existing->name, // Duplicate name
            'slug' => $existing->slug, // Duplicate slug
        ]);

        TestHelpers::assertApiValidationError($response);
    }

    /**
     * Test admin can view tag details.
     */
    public function test_admin_can_view_tag_details(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tag = Tag::factory()->create();

        $response = $this->getJson("/api/v1/manage/library/tags/{$tag->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $tag->id,
                'name' => $tag->name,
            ],
        ]);
    }

    /**
     * Test admin can update tag.
     */
    public function test_admin_can_update_tag(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tag = Tag::factory()->create();

        $response = $this->putJson("/api/v1/manage/library/tags/{$tag->id}", [
            'name' => 'Updated Tag Name',
            'slug' => 'updated-tag-name-'.uniqid(),
        ]);

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('lib_tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag Name',
        ]);
    }

    /**
     * Test admin can delete tag.
     */
    public function test_admin_can_delete_tag(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tag = Tag::factory()->create();

        $response = $this->deleteJson("/api/v1/manage/library/tags/{$tag->id}");

        TestHelpers::assertApiSuccess($response);
        $this->assertSoftDeleted('lib_tags', [
            'id' => $tag->id,
        ]);
    }

    /**
     * Test admin can bulk delete tags.
     */
    public function test_admin_can_bulk_delete_tags(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tags = Tag::factory()->count(3)->create();
        $ids = $tags->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/manage/library/tags/bulk-delete', [
            'ids' => $ids,
        ]);

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test public can list tags (for frontend).
     */
    public function test_public_can_list_tags(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/public/library/tags');

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test unauthenticated user cannot create tags.
     */
    public function test_unauthenticated_user_cannot_create_tags(): void
    {
        $response = $this->postJson('/api/v1/manage/library/tags', [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test user without permission cannot manage tags.
     */
    public function test_user_without_permission_cannot_manage_tags(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/manage/library/tags', [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_search_tags_short_term_does_not_error(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/v1/manage/library/tags?page=1&per_page=20&search=Jejakawan');
        TestHelpers::assertApiSuccess($response);
    }

    public function test_tag_list_includes_contents_count_from_pivot(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $tag = Tag::factory()->create(['name' => 'UsageTag_'.bin2hex(random_bytes(4))]);
        $unused = Tag::factory()->create(['name' => 'UnusedTag_'.bin2hex(random_bytes(4))]);

        $contentClass = Content::class;
        $content = $contentClass::factory()->create();
        $content->tags()->attach($tag->id);

        $response = $this->getJson('/api/v1/manage/library/tags?per_page=100');
        TestHelpers::assertApiSuccess($response);

        $rows = collect($response->json('data.data') ?? $response->json('data'));
        $usedRow = $rows->firstWhere('id', $tag->id);
        $unusedRow = $rows->firstWhere('id', $unused->id);

        $this->assertNotNull($usedRow);
        $this->assertSame(1, $usedRow['contents_count'] ?? null);
        $this->assertNotNull($unusedRow);
        $this->assertSame(0, $unusedRow['contents_count'] ?? null);

        $stats = $this->getJson('/api/v1/manage/library/tags/statistics');
        TestHelpers::assertApiSuccess($stats);
        $this->assertGreaterThanOrEqual(1, $stats->json('data.total_usage'));
        $this->assertGreaterThanOrEqual(1, $stats->json('data.used_tags'));
    }
}
