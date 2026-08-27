<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Modules\Core\System\Models\Extension;
use Modules\Layout\Models\Widget;
use Modules\Publishing\Models\Content;
use Tests\TestCase;

class PublicWidgetRuntimeTest extends TestCase
{
    public function test_public_widgets_empty_when_layout_pack_inactive(): void
    {
        Extension::query()->updateOrCreate(
            ['slug' => 'layout'],
            [
                'type' => 'module',
                'name' => 'Layout',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'inactive',
                'is_core' => false,
            ]
        );
        Extension::flushProductActiveMemo();

        Widget::factory()->create([
            'name' => 'Hidden sidebar',
            'type' => 'text',
            'location' => 'sidebar',
            'settings' => ['content' => 'Secret'],
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/public/layout/widgets/location/sidebar')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_public_widgets_render_when_layout_is_active(): void
    {
        $this->activatePack('layout');

        Widget::factory()->create([
            'name' => 'About the site',
            'type' => 'text',
            'location' => 'sidebar',
            'settings' => ['content' => 'Hello visitors'],
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/public/layout/widgets/location/sidebar')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'About the site')
            ->assertJsonPath('data.0.content', 'Hello visitors');
    }

    public function test_recent_posts_widget_includes_published_items(): void
    {
        $this->activatePack('layout');
        $this->activatePack('publishing');

        Content::factory()->published()->create([
            'title' => 'Public widget post',
            'type' => 'post',
            'category_id' => null,
        ]);

        Widget::factory()->create([
            'name' => 'Latest',
            'type' => 'recent_posts',
            'location' => 'sidebar',
            'settings' => ['count' => 3],
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/public/layout/widgets/location/sidebar?module=Jejakawan')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'recent_posts');

        $items = $this->getJson('/api/v1/public/layout/widgets/location/sidebar')->json('data.0.items');
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $this->assertSame('Public widget post', $items[0]['title']);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function activatePack(string $slug, array $extra = []): void
    {
        Extension::query()->updateOrCreate(
            ['slug' => $slug],
            array_merge([
                'type' => 'module',
                'name' => ucfirst($slug),
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_core' => false,
            ], $extra)
        );
        Extension::flushProductActiveMemo();
    }
}
