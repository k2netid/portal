<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Modules\Content\Layout\Services\PluginThemeBlocksService;
use Modules\Core\System\Models\Plugin;
use Tests\TestCase;

class PluginThemeBlocksServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): PluginThemeBlocksService
    {
        return $this->app->make(PluginThemeBlocksService::class);
    }

    public function test_manifest_includes_active_plugin_with_default_slots(): void
    {
        Plugin::create([
            'name' => 'Content Share Bar',
            'slug' => 'content-share-bar',
            'is_active' => true,
            'priority' => 20,
            'settings' => [],
        ]);

        $manifest = $this->service()->getPublicManifest();

        $this->assertNotEmpty($manifest);
        $this->assertSame('content-share-bar', $manifest[0]['slug']);
        $this->assertContains('after_post_content', $manifest[0]['slots']);
    }

    public function test_inactive_plugin_excluded(): void
    {
        Plugin::create([
            'name' => 'Inactive',
            'slug' => 'content-share-bar',
            'is_active' => false,
            'priority' => 99,
            'settings' => [],
        ]);

        $this->assertSame([], $this->service()->getPublicManifest());
    }

    public function test_manifest_includes_validated_blocks_url_when_feature_enabled(): void
    {
        Config::set('layout.remote_plugin_blocks.enabled', true);
        Config::set('layout.remote_plugin_blocks.allowed_hosts', ['cdn.example.com']);

        Plugin::create([
            'name' => 'Remote Blocks Plugin',
            'slug' => 'remote-blocks-demo',
            'is_active' => true,
            'priority' => 10,
            'settings' => [
                'theme_blocks' => [['slot' => 'before_footer']],
                'theme_blocks_remote_url' => 'https://cdn.example.com/plugins/remote-blocks-demo/blocks.js',
            ],
        ]);

        $manifest = $this->service()->getPublicManifest();
        $this->assertSame('https://cdn.example.com/plugins/remote-blocks-demo/blocks.js', $manifest[0]['blocks_url'] ?? null);
    }
}
