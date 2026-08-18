<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Plugin;
use Tests\TestCase;

class PublicPluginBlocksApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_plugin_blocks_endpoint_returns_active_plugins(): void
    {
        Plugin::create([
            'name' => 'Content Share Bar',
            'slug' => 'content-share-bar',
            'is_active' => true,
            'priority' => 20,
            'settings' => ['theme_blocks' => [['slot' => 'after_post_content']]],
        ]);

        $response = $this->getJson('/api/v1/public/layout/plugin-blocks');

        $response->assertOk();
        $response->assertJsonPath('data.plugins.0.slug', 'content-share-bar');
        $response->assertJsonFragment(['after_post_content']);
    }
}
