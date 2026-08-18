<?php

namespace Modules\Content\Publishing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class PublicContentShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserved_page_without_body_returns_fallback_not_404(): void
    {
        $user = User::factory()->create();

        Content::factory()->create([
            'slug' => 'about',
            'type' => 'page',
            'status' => 'published',
            'author_id' => $user->id,
            'body' => null,
            'intro' => null,
            'excerpt' => null,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/public/publishing/contents/about');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null)
            ->assertJsonFragment(['message' => 'About content not found, using fallback']);
    }

    public function test_non_reserved_page_without_body_returns_404(): void
    {
        $user = User::factory()->create();

        Content::factory()->create([
            'slug' => 'empty-shell',
            'type' => 'page',
            'status' => 'published',
            'author_id' => $user->id,
            'body' => null,
            'intro' => null,
            'excerpt' => null,
            'featured_image' => null,
            'published_at' => now(),
        ]);

        $this->getJson('/api/v1/public/publishing/contents/empty-shell')
            ->assertNotFound();
    }
}
