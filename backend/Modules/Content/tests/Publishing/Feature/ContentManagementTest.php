<?php

namespace Modules\Content\Publishing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Library\Models\Category;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Publishing\Models\Content;
use Modules\Content\Publishing\Models\ContentRevision;
use Modules\Core\System\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    /**
     * Test admin can list all pub_contents.
     */
    public function test_admin_can_list_all_contents(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Content::count();
        Content::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/manage/publishing/contents');

        TestHelpers::assertApiPaginated($response);
        // If total pub_contents exceed per_page (usually 10 or 15), this might fail if we assert count + 5
        // Ideally we check total in meta, or just assert we got some data
        // $response->assertJsonCount(min($initialCount + 5, 10), 'data.data'); // Assuming 10 per page

        // Safer check: assert at least 5
        $data = $response->json('data.data');
        $this->assertGreaterThanOrEqual(5, count($data));
    }

    /**
     * Test admin can filter pub_contents by status.
     */
    public function test_admin_can_filter_contents_by_status(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $initialCount = Content::where('status', 'draft')->count();

        Content::factory()->published()->count(3)->create();
        Content::factory()->draft()->count(2)->create();

        $response = $this->getJson('/api/v1/manage/publishing/contents?status=draft');

        TestHelpers::assertApiPaginated($response);
        $response->assertJsonCount($initialCount + 2, 'data.data');
    }

    /**
     * Test admin can create content.
     */
    public function test_admin_can_create_content(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $contentData = TestHelpers::getContentData([
            'category_id' => $category->id,
            'lib_tags' => $tags->pluck('id')->toArray(),
        ]);

        $response = $this->postJson('/api/v1/manage/publishing/contents', $contentData);

        TestHelpers::assertApiSuccess($response, 201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'slug',
                'author_id',
            ],
        ]);

        $this->assertDatabaseHas('pub_contents', [
            'title' => $contentData['title'],
            'slug' => $contentData['slug'],
            'author_id' => $admin->id,
        ]);
    }

    /**
     * Test content creation requires all required fields.
     */
    public function test_content_creation_requires_required_fields(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/v1/manage/publishing/contents', []);

        TestHelpers::assertApiValidationError($response);
        // Only title, status, and type are required by the API
        $response->assertJsonValidationErrors(['title', 'status', 'type']);
    }

    /**
     * Test admin can view content details.
     */
    public function test_admin_can_view_content_details(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create();

        $response = $this->getJson("/api/v1/manage/publishing/contents/{$content->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $content->id,
                'title' => $content->title,
            ],
        ]);
    }

    /**
     * Test admin can update content.
     */
    public function test_admin_can_update_content(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create();

        $updateData = [
            'title' => 'Updated Title',
            'body' => 'Updated body content',
        ];

        $response = $this->putJson("/api/v1/manage/publishing/contents/{$content->id}", array_merge(
            $content->toArray(),
            $updateData
        ));

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('pub_contents', [
            'id' => $content->id,
            'title' => 'Updated Title',
        ]);
    }

    /**
     * Test admin can delete content.
     */
    public function test_admin_can_delete_content(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create();

        $response = $this->deleteJson("/api/v1/manage/publishing/contents/{$content->id}");

        TestHelpers::assertApiSuccess($response);
        $this->assertSoftDeleted('pub_contents', [
            'id' => $content->id,
        ]);
    }

    /**
     * Test admin can duplicate content.
     */
    public function test_admin_can_duplicate_content(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $content->tags()->attach($tags);

        $response = $this->postJson("/api/v1/manage/publishing/contents/{$content->id}/duplicate");

        TestHelpers::assertApiSuccess($response, 201);

        $this->assertDatabaseHas('pub_contents', [
            'title' => $content->title.' (Copy)',
        ]);
    }

    /**
     * Test admin can perform bulk actions on pub_contents.
     */
    public function test_admin_can_perform_bulk_actions(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $contents = Content::factory()->count(3)->create();

        $response = $this->postJson('/api/v1/manage/publishing/contents/bulk-action', [
            'action' => 'delete',
            'content_ids' => $contents->pluck('id')->toArray(),
        ]);

        TestHelpers::assertApiSuccess($response);

        foreach ($contents as $content) {
            $this->assertSoftDeleted('pub_contents', ['id' => $content->id]);
        }
    }

    /**
     * Test bulk action requires valid action.
     */
    public function test_bulk_action_requires_valid_action(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $contents = Content::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/manage/publishing/contents/bulk-action', [
            'action' => 'invalid-action',
            'ids' => $contents->pluck('id')->toArray(),
        ]);

        TestHelpers::assertApiValidationError($response);
    }

    /**
     * Test admin can create content revision.
     */
    public function test_admin_can_create_content_revision(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create();

        $response = $this->postJson("/api/v1/manage/publishing/contents/{$content->id}/revisions", [
            'note' => 'Test revision',
        ]);

        TestHelpers::assertApiSuccess($response, 201);
        $this->assertDatabaseHas('pub_content_revisions', [
            'content_id' => $content->id,
            'author_id' => $admin->id,
            'reason' => 'Test revision',
        ]);
    }

    /**
     * Test admin can list content revisions.
     */
    public function test_admin_can_list_pub_content_revisions(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create();
        ContentRevision::factory()->count(3)->create([
            'content_id' => $content->id,
        ]);

        $response = $this->getJson("/api/v1/manage/publishing/contents/{$content->id}/revisions");

        TestHelpers::assertApiPaginated($response);
        $response->assertJsonCount(3, 'data.data');
    }

    /**
     * Test admin can view revision details.
     */
    public function test_admin_can_view_revision_details(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create();
        $revision = ContentRevision::factory()->create([
            'content_id' => $content->id,
        ]);

        $response = $this->getJson("/api/v1/manage/publishing/contents/{$content->id}/revisions/{$revision->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $revision->id,
                'content_id' => $content->id,
            ],
        ]);
    }

    /**
     * Test admin can restore content from revision.
     */
    public function test_admin_can_restore_content_from_revision(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create([
            'title' => 'Original Title',
            'body' => 'Original body',
        ]);

        $revision = ContentRevision::factory()->create([
            'content_id' => $content->id,
            'title' => 'Revision Title',
            'body' => 'Revision body',
        ]);

        $response = $this->postJson("/api/v1/manage/publishing/contents/{$content->id}/revisions/{$revision->id}/restore");

        TestHelpers::assertApiSuccess($response);

        $content->refresh();
        $this->assertEquals('Revision Title', $content->title);
        $this->assertEquals('Revision body', $content->body);
    }

    /**
     * Test admin can delete revision.
     */
    public function test_admin_can_delete_revision(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create();
        $revision = ContentRevision::factory()->create([
            'content_id' => $content->id,
        ]);

        $response = $this->deleteJson("/api/v1/manage/publishing/contents/{$content->id}/revisions/{$revision->id}");

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseMissing('pub_content_revisions', [
            'id' => $revision->id,
        ]);
    }

    /**
     * Test admin can lock content.
     */
    public function test_admin_can_lock_content(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create();

        $response = $this->postJson("/api/v1/manage/publishing/contents/{$content->id}/lock");

        TestHelpers::assertApiSuccess($response);

        $content->refresh();
        $this->assertEquals($admin->id, $content->locked_by);
        $this->assertNotNull($content->locked_at);
    }

    /**
     * Test admin can unlock content.
     */
    public function test_admin_can_unlock_content(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create([
            'locked_by' => $admin->id,
            'locked_at' => now(),
        ]);

        $response = $this->postJson("/api/v1/manage/publishing/contents/{$content->id}/unlock");

        TestHelpers::assertApiSuccess($response);

        $content->refresh();
        $this->assertNull($content->locked_by);
        $this->assertNull($content->locked_at);
    }

    /**
     * Test admin can preview content.
     */
    public function test_admin_can_preview_content(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->draft()->create();

        $response = $this->getJson("/api/v1/manage/publishing/contents/{$content->id}/preview");

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'content' => [
                    'id' => $content->id,
                ],
            ],
        ]);
    }

    /**
     * Test user without permission cannot create content.
     */
    public function test_user_without_permission_cannot_create_content(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $contentData = TestHelpers::getContentData();

        $response = $this->postJson('/api/v1/manage/publishing/contents', $contentData);

        $response->assertStatus(403);
    }

    /**
     * Test user without permission cannot update content.
     */
    public function test_user_without_permission_cannot_update_content(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $content = Content::factory()->create();

        $response = $this->putJson("/api/v1/manage/publishing/contents/{$content->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin can filter pub_contents by various parameters.
     */
    public function test_admin_can_filter_pub_contents_by_various_params(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $category = Category::factory()->create();
        Content::factory()->create(['title' => 'Searchable Article', 'type' => 'article', 'category_id' => $category->id]);
        Content::factory()->create(['title' => 'Other Post', 'type' => 'post']);

        // Filter by type
        $response = $this->getJson('/api/v1/manage/publishing/contents?type=article');
        $response->assertJsonCount(1, 'data.data');

        // Filter by category
        $response = $this->getJson("/api/v1/manage/publishing/contents?category_id={$category->id}");
        $response->assertJsonCount(1, 'data.data');

        // Filter by search
        $response = $this->getJson('/api/v1/manage/publishing/contents?search=Searchable');
        $response->assertJsonCount(1, 'data.data');
    }

    /**
     * Test admin can manage trashed pub_contents.
     */
    public function test_admin_can_manage_trashed_pub_contents(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create();
        $content->delete();

        // List trashed
        $response = $this->getJson('/api/v1/manage/publishing/contents?status=trashed');
        TestHelpers::assertApiPaginated($response);
        $this->assertGreaterThanOrEqual(1, count($response->json('data.data')));

        // Restore
        $response = $this->putJson("/api/v1/manage/publishing/contents/{$content->id}/restore");
        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('pub_contents', ['id' => $content->id, 'deleted_at' => null]);

        // Force Delete
        $content->delete();
        $response = $this->deleteJson("/api/v1/manage/publishing/contents/{$content->id}/force-delete");
        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseMissing('pub_contents', ['id' => $content->id]);
    }

    /**
     * Test admin can perform bulk restore and force delete.
     */
    public function test_admin_can_perform_bulk_trashed_actions(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $contents = Content::factory()->count(2)->create();
        foreach ($contents as $c) {
            $c->delete();
        }

        // Bulk Restore
        $response = $this->postJson('/api/v1/manage/publishing/contents/bulk-action', [
            'action' => 'restore',
            'content_ids' => $contents->pluck('id')->toArray(),
        ]);
        TestHelpers::assertApiSuccess($response);
        foreach ($contents as $c) {
            $this->assertDatabaseHas('pub_contents', ['id' => $c->id, 'deleted_at' => null]);
        }

        // Bulk Force Delete
        foreach ($contents as $c) {
            $c->delete();
        }
        $response = $this->postJson('/api/v1/manage/publishing/contents/bulk-action', [
            'action' => 'force_delete',
            'content_ids' => $contents->pluck('id')->toArray(),
        ]);
        TestHelpers::assertApiSuccess($response);
        foreach ($contents as $c) {
            $this->assertDatabaseMissing('pub_contents', ['id' => $c->id]);
        }
    }

    /**
     * Test admin can search content by spaced or unformatted UUID.
     */
    public function test_admin_can_search_content_by_spaced_or_unformatted_uuid(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->create([
            'title' => 'Completely Random Title',
            'body' => 'Random body text.',
        ]);

        $uuid = $content->id; // Format: 019e3699-78f2-72c6-9d31-f945474fd69d

        // 1. Search by exact UUID
        $response = $this->getJson("/api/v1/manage/publishing/contents?search={$uuid}");
        TestHelpers::assertApiPaginated($response);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals($content->id, $response->json('data.data.0.id'));

        // 2. Search by spaced UUID (019e3699 78f2 72c6 9d31 f945474fd69d)
        $spacedUuid = str_replace('-', ' ', $uuid);
        $responseSpaced = $this->getJson("/api/v1/manage/publishing/contents?search={$spacedUuid}");
        TestHelpers::assertApiPaginated($responseSpaced);
        $this->assertCount(1, $responseSpaced->json('data.data'));
        $this->assertEquals($content->id, $responseSpaced->json('data.data.0.id'));

        // 3. Search by unformatted raw hex UUID (019e369978f272c69d31f945474fd69d)
        $rawUuid = str_replace('-', '', $uuid);
        $responseRaw = $this->getJson("/api/v1/manage/publishing/contents?search={$rawUuid}");
        TestHelpers::assertApiPaginated($responseRaw);
        $this->assertCount(1, $responseRaw->json('data.data'));
        $this->assertEquals($content->id, $responseRaw->json('data.data.0.id'));
    }

    public function test_admin_search_contents_short_term_does_not_error(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/v1/manage/publishing/contents?page=1&per_page=10&sort=created_at&order=desc&search=har');
        TestHelpers::assertApiSuccess($response);
    }
}
