<?php

namespace Modules\Content\Layout\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Layout\Services\DynamicTagService;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class DynamicTagServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DynamicTagService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DynamicTagService;
    }

    public function test_resolve_post_tags(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $content = Content::factory()->create([
            'title' => 'Test Post',
            'excerpt' => 'This is an excerpt',
            'author_id' => $user->id,
            'published_at' => now()->startOfDay(),
        ]);

        $this->assertEquals('Test Post', $this->service->resolveTag('{{post_title}}', $content));
        $this->assertEquals('John Doe', $this->service->resolveTag('{{post_author}}', $content));
        $this->assertEquals(now()->format('M d, Y'), $this->service->resolveTag('{{post_date}}', $content));
    }

    public function test_resolve_site_tags(): void
    {
        Setting::set('site_title', 'My Awesome Site');
        Setting::set('site_tagline', 'The best site ever');

        $this->assertEquals('My Awesome Site', $this->service->resolveTag('{{site_title}}'));
        $this->assertEquals('The best site ever', $this->service->resolveTag('{{site_tagline}}'));
        $this->assertEquals(now()->year, $this->service->resolveTag('{{current_year}}'));
    }

    public function test_resolve_loop_tags(): void
    {
        $loopItem = [
            'title' => 'Loop Title',
            'excerpt' => 'Loop Excerpt',
            'index' => '1',
        ];

        $this->assertEquals('Loop Title', $this->service->resolveTag('{{loop_title}}', null, $loopItem));
        $this->assertEquals('1', $this->service->resolveTag('{{loop_index}}', null, $loopItem));
    }

    public function test_resolve_user_tags(): void
    {
        $user = User::factory()->create(['name' => 'Active User', 'email' => 'user@example.com']);
        $this->actingAs($user);

        $this->assertEquals('Active User', $this->service->resolveTag('{{user_name}}'));
        $this->assertEquals('user@example.com', $this->service->resolveTag('{{user_email}}'));
    }

    public function test_resolve_blocks_recursively(): void
    {
        $blocks = [
            [
                'type' => 'header',
                'settings' => [
                    'title' => '@dynamic:{{site_title}}',
                ],
                'children' => [
                    [
                        'type' => 'text',
                        'settings' => [
                            'content' => '@dynamic:{{current_year}}',
                        ],
                    ],
                ],
            ],
        ];

        Setting::set('site_title', 'Block Test Site');

        $resolved = $this->service->resolveBlocks($blocks);

        $this->assertEquals('Block Test Site', $resolved[0]['settings']['title']);
        $this->assertEquals(now()->year, $resolved[0]['children'][0]['settings']['content']);
    }

    public function test_resolve_archive_tags(): void
    {
        $this->assertEquals('Archive', $this->service->resolveTag('{{archive_title}}'));
    }
}
