<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Publishing\Contracts\PublishedContentAnalyticsPortInterface;
use Modules\Content\Publishing\Models\Content;
use Tests\TestCase;

class PublishedContentAnalyticsPortTest extends TestCase
{
    use RefreshDatabase;

    public function test_port_returns_published_rows_by_slug(): void
    {
        Content::factory()->published()->create([
            'title' => 'Analytics Port Post',
            'slug' => 'analytics-port-post',
            'type' => 'post',
        ]);
        Content::factory()->create([
            'title' => 'Draft Hidden',
            'slug' => 'draft-hidden',
            'status' => 'draft',
        ]);

        $port = app(PublishedContentAnalyticsPortInterface::class);
        $rows = $port->publishedRowsBySlugs(['analytics-port-post', 'draft-hidden']);

        $this->assertCount(1, $rows);
        $this->assertSame('analytics-port-post', $rows[0]->slug);
        $this->assertSame('Analytics Port Post', $rows[0]->title);
    }
}
