<?php

namespace Modules\Content\Publishing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Modules\Content\Library\Models\Category;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class SeoToolsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        // Create admin user with permissions
        $this->admin = $this->createAdminUser();
    }

    use RefreshDatabase;

    protected User $admin;

    /**
     * Test admin can generate sitemap.
     */
    public function test_admin_can_generate_sitemap(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/publishing/seo/sitemap');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'url',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'url' => url('/sitemap.xml'),
            ],
        ]);
    }

    /**
     * Test admin can get robots.txt content.
     */
    public function test_admin_can_get_robots_txt(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/publishing/seo/robots-txt');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'content',
            ],
        ]);

        // Should contain default robots.txt content
        $data = $response->json('data');
        $this->assertStringContainsString('User-agent:', $data['content']);
        $this->assertStringContainsString('Sitemap:', $data['content']);
    }

    /**
     * Test admin can update robots.txt content.
     */
    public function test_admin_can_update_robots_txt(): void
    {
        $newContent = "User-agent: *\nDisallow: /admin\n\nSitemap: ".url('/sitemap.xml');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/v1/manage/publishing/seo/robots-txt', [
                'content' => $newContent,
            ]);

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'content' => $newContent,
            ],
        ]);

        // Verify file was created/updated
        $this->assertTrue(File::exists(public_path('robots.txt')));
        $this->assertEquals($newContent, File::get(public_path('robots.txt')));

        // Cleanup
        File::delete(public_path('robots.txt'));
    }

    /**
     * Test robots.txt update requires content field.
     */
    public function test_robots_txt_update_requires_content(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/v1/manage/publishing/seo/robots-txt', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    /**
     * Test admin can analyze content SEO.
     */
    public function test_admin_can_analyze_content_seo(): void
    {
        $content = Content::factory()->create([
            'title' => 'This is a good SEO title with proper length',
            'meta_title' => 'Good Meta Title With Proper Length Here',
            'meta_description' => 'This is a meta description with proper length between 120 and 160 characters. It describes the content well and includes relevant keywords.',
            'meta_keywords' => 'seo, testing, laravel, Jejakawan, content',
            'excerpt' => 'This is a good excerpt for the content',
            'body' => str_repeat('This is the content body with sufficient length for SEO analysis. ', 20),
            'slug' => 'good-seo-title-proper-length',
            'featured_image' => '/storage/images/test.jpg',
            'og_image' => '/storage/images/og-test.jpg',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/publishing/contents/{$content->id}/seo-analysis");

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'score',
                'max_score',
                'percentage',
                'grade',
                'issues',
                'suggestions',
            ],
        ]);

        // Should have a good score
        $data = $response->json('data');
        $this->assertGreaterThan(80, $data['score']);
        $this->assertEquals(100, $data['max_score']);
        $this->assertIsArray($data['issues']);
        $this->assertIsArray($data['suggestions']);
    }

    /**
     * Test SEO analysis detects missing meta data.
     */
    public function test_seo_analysis_detects_missing_meta_data(): void
    {
        $content = Content::factory()->create([
            'title' => 'Short',
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'excerpt' => null,
            'body' => 'Too short',
            'slug' => 'short',
            'featured_image' => null,
            'og_image' => null,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/publishing/contents/{$content->id}/seo-analysis");

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');
        $this->assertLessThan(50, $data['score']); // Should have low score
        $this->assertNotEmpty($data['issues']); // Should have issues
        $this->assertNotEmpty($data['suggestions']); // Should have suggestions
    }

    /**
     * Test admin can generate schema for content.
     */
    public function test_admin_can_generate_schema_for_content(): void
    {
        $category = Category::factory()->create(['name' => 'Technology']);

        $content = Content::factory()->create([
            'title' => 'Test Article Title',
            'meta_description' => 'This is a test article meta description',
            'excerpt' => 'Test excerpt',
            'body' => 'Test body content',
            'slug' => 'test-article-title',
            'featured_image' => '/storage/images/test.jpg',
            'status' => 'published',
            'type' => 'post',
            'category_id' => $category->id,
            'author_id' => $this->admin->id,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/publishing/contents/{$content->id}/schema");

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');

        // Verify schema structure
        $this->assertEquals('https://schema.org', $data['@context']);
        $this->assertEquals('BlogPosting', $data['@type']);
        $this->assertEquals($content->title, $data['headline']);
        $this->assertEquals($content->meta_description, $data['description']);
        $this->assertArrayHasKey('datePublished', $data);
        $this->assertArrayHasKey('dateModified', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertArrayHasKey('image', $data);
        $this->assertArrayHasKey('articleSection', $data);
        $this->assertEquals($category->name, $data['articleSection']);
    }

    /**
     * Test schema generation for article type.
     */
    public function test_schema_generation_for_article_type(): void
    {
        $content = Content::factory()->create([
            'type' => 'article',
            'title' => 'Test Article',
            'status' => 'published',
            'author_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/publishing/contents/{$content->id}/schema");

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');
        $this->assertEquals('Article', $data['@type']);
    }

    /**
     * Test user without permission cannot access SEO tools.
     */
    public function test_user_without_permission_cannot_update_robots_txt(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/manage/publishing/seo/robots-txt', [
                'content' => 'Test content',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated user cannot access SEO tools.
     */
    public function test_unauthenticated_user_cannot_access_seo_tools(): void
    {
        $response = $this->getJson('/api/v1/manage/publishing/seo/sitemap');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/manage/publishing/seo/robots-txt');
        $response->assertStatus(401);

        $response = $this->putJson('/api/v1/manage/publishing/seo/robots-txt', [
            'content' => 'Test',
        ]);
        $response->assertStatus(401);
    }

    /**
     * Clean up after tests.
     */
    protected function tearDown(): void
    {
        // Clean up any created robots.txt file
        if (File::exists(public_path('robots.txt'))) {
            File::delete(public_path('robots.txt'));
        }

        parent::tearDown();
    }
}
