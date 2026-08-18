<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Publishing\Dto\SearchableContentSnapshot;
use Modules\Content\Publishing\Models\Content;
use Tests\TestCase;

class SearchableContentSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_from_content_builds_snapshot(): void
    {
        $content = Content::factory()->create([
            'status' => 'published',
            'title' => 'Snapshot Article',
            'slug' => 'snapshot-article',
            'type' => 'post',
            'intro' => 'Intro text',
            'body' => '<p>Body</p>',
        ]);

        $snapshot = SearchableContentSnapshot::fromContent($content);

        $this->assertSame(Content::class, $snapshot->searchableType);
        $this->assertSame($content->id, $snapshot->searchableId);
        $this->assertSame('Snapshot Article', $snapshot->title);
        $this->assertSame('published', $snapshot->status);
    }
}
