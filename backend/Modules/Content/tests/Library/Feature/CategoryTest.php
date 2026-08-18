<?php

namespace Modules\Content\Library\Tests\Feature;

use Modules\Content\Library\Models\Category;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    /**
     * Test admin can list all lib_categories.
     */
    public function test_admin_can_list_all_lib_categories(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/manage/library/categories');

        TestHelpers::assertApiSuccess($response);
        $this->assertIsArray($response->json('data'));
    }

    /**
     * Test admin can get lib_categories as tree.
     */
    public function test_admin_can_get_lib_categories_tree(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $parent = Category::factory()->create(['parent_id' => null]);
        Category::factory()->count(2)->create(['parent_id' => $parent->id]);

        $response = $this->getJson('/api/v1/manage/library/categories?tree=1');

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test admin can create category.
     */
    public function test_admin_can_create_category(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $categoryData = [
            'name' => 'Test Category '.uniqid(),
            'slug' => 'test-category-'.uniqid(),
            'description' => 'Test description',
            'is_active' => true,
            'sort_order' => 1,
        ];

        $response = $this->postJson('/api/v1/manage/library/categories', $categoryData);

        TestHelpers::assertApiSuccess($response, 201);
        $this->assertDatabaseHas('lib_categories', [
            'name' => $categoryData['name'],
            'slug' => $categoryData['slug'],
        ]);
    }

    /**
     * Test category creation requires name and slug.
     */
    public function test_category_creation_requires_name_and_slug(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/v1/manage/library/categories', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['name', 'slug']);
    }

    /**
     * Test category slug must be unique.
     */
    public function test_category_slug_must_be_unique(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $existing = Category::factory()->create();

        $response = $this->postJson('/api/v1/manage/library/categories', [
            'name' => 'New Category',
            'slug' => $existing->slug, // Duplicate slug
        ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['slug']);
    }

    /**
     * Test admin can view category details.
     */
    public function test_admin_can_view_category_details(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/manage/library/categories/{$category->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ]);
    }

    /**
     * Test admin can update category.
     */
    public function test_admin_can_update_category(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $category = Category::factory()->create();

        $response = $this->putJson("/api/v1/manage/library/categories/{$category->id}", [
            'name' => 'Updated Category Name',
        ]);

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('lib_categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
        ]);
    }

    /**
     * Test category cannot be its own parent.
     */
    public function test_category_cannot_be_its_own_parent(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $category = Category::factory()->create();

        $response = $this->putJson("/api/v1/manage/library/categories/{$category->id}", [
            'parent_id' => $category->id,
        ]);

        TestHelpers::assertApiValidationError($response);
    }

    /**
     * Test admin can delete empty category.
     */
    public function test_admin_can_delete_empty_category(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/manage/library/categories/{$category->id}");

        TestHelpers::assertApiSuccess($response);
        $this->assertSoftDeleted('lib_categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test cannot delete category with children.
     */
    public function test_cannot_delete_category_with_children(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        $response = $this->deleteJson("/api/v1/manage/library/categories/{$parent->id}");

        TestHelpers::assertApiValidationError($response);
    }

    /**
     * Test cannot delete category with pub_contents.
     */
    public function test_cannot_delete_category_with_pub_contents(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $category = Category::factory()->create();
        Content::factory()->create(['category_id' => $category->id]);

        $response = $this->deleteJson("/api/v1/manage/library/categories/{$category->id}");

        TestHelpers::assertApiValidationError($response);
    }

    /**
     * Test admin can move category.
     */
    public function test_admin_can_move_category(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $parent = Category::factory()->create();
        $category = Category::factory()->create();

        $response = $this->postJson("/api/v1/manage/library/categories/{$category->id}/move", [
            'parent_id' => $parent->id,
            'sort_order' => 5,
        ]);

        TestHelpers::assertApiSuccess($response);

        $category->refresh();
        $this->assertEquals($parent->id, $category->parent_id);
        $this->assertEquals(5, $category->sort_order);
    }

    /**
     * Test admin can bulk delete lib_categories.
     */
    public function test_admin_can_bulk_delete_lib_categories(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $lib_categories = Category::factory()->count(3)->create();
        $ids = $lib_categories->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/manage/library/categories/bulk-destroy', [
            'ids' => $ids,
        ]);

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test unauthenticated user cannot manage lib_categories.
     */
    public function test_unauthenticated_user_cannot_manage_lib_categories(): void
    {
        $response = $this->postJson('/api/v1/manage/library/categories', [
            'name' => 'Test',
            'slug' => 'test',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test user without permission cannot manage lib_categories.
     */
    public function test_user_without_permission_cannot_manage_lib_categories(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/manage/library/categories', [
            'name' => 'Test',
            'slug' => 'test-'.uniqid(),
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin can filter lib_categories by search.
     */
    public function test_admin_can_search_lib_categories(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $uniqueName = 'uniquecat'.time();
        Category::factory()->create(['name' => $uniqueName, 'is_active' => true]);
        Category::factory()->create(['name' => 'nonmatching'.time(), 'is_active' => true]);

        $response = $this->getJson('/api/v1/manage/library/categories?search='.$uniqueName);
        TestHelpers::assertApiSuccess($response);
        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test admin can manage trashed lib_categories.
     */
    public function test_admin_can_manage_trashed_lib_categories(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $category = Category::factory()->create();
        $category->delete();

        // Restore
        $response = $this->putJson("/api/v1/manage/library/categories/{$category->id}/restore");
        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('lib_categories', ['id' => $category->id, 'deleted_at' => null]);

        // Force Delete
        $category->delete();
        $response = $this->deleteJson("/api/v1/manage/library/categories/{$category->id}/force-delete");
        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseMissing('lib_categories', ['id' => $category->id]);
    }

    /**
     * Short search terms must not 500 on PostgreSQL (LIKE binding).
     */
    public function test_admin_search_lib_categories_short_term_does_not_error(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/v1/manage/library/categories?page=1&per_page=5&tree=false&search=ne');
        TestHelpers::assertApiSuccess($response);
    }
}
