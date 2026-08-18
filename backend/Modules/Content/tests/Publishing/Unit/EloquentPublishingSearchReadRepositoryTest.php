<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Publishing\Dto\SearchableContentSnapshot;
use Modules\Content\Publishing\Models\Content;
use Modules\Content\Publishing\Repositories\EloquentPublishingSearchReadRepository;
use Tests\TestCase;

class EloquentPublishingSearchReadRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_snapshots_only_yields_published_content(): void
    {
        $this->seedPermissionsAndRoles();
        $admin = $this->createAdminUser();

        Content::factory()->create([
            'title' => 'Draft post',
            'slug' => 'draft-post',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        $published = Content::factory()->published()->create([
            'title' => 'Live post',
            'slug' => 'live-post',
            'author_id' => $admin->id,
        ]);

        $repo = new EloquentPublishingSearchReadRepository;
        $snapshots = iterator_to_array($repo->publishedSnapshots());

        $this->assertCount(1, $snapshots);
        $this->assertInstanceOf(SearchableContentSnapshot::class, $snapshots[0]);
        $this->assertSame((string) $published->getKey(), $snapshots[0]->searchableId);
        $this->assertSame('Live post', $snapshots[0]->title);
    }

    public function test_snapshot_by_id_returns_null_when_missing(): void
    {
        $repo = new EloquentPublishingSearchReadRepository;

        $this->assertNull($repo->snapshotById('missing-id'));
    }
}
