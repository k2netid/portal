<?php

declare(strict_types=1);

namespace Modules\Publishing\Tests\Feature;

use Modules\Publishing\Models\Content;
use Modules\Publishing\Models\ContentRevision;
use Tests\TestCase;

class BuilderRevisionAndLockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->activatePack('publishing');
    }

    public function test_builder_save_creates_revision_with_builder_blocks(): void
    {
        $admin = $this->createAdminUser();
        $firstTree = [[
            'id' => 'module-hero',
            'type' => 'section',
            'settings' => [],
            'children' => [[
                'id' => 'module-heading',
                'type' => 'heading',
                'settings' => ['text' => 'Hello'],
            ]],
        ]];
        $content = Content::factory()->create([
            'author_id' => $admin->id,
            'category_id' => null,
            'type' => 'page',
            'status' => 'draft',
            'meta' => ['builder_blocks' => $firstTree],
        ]);

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/manage/publishing/contents/'.$content->id, [
                'title' => $content->title,
                'create_revision' => true,
                'revision_note' => 'Builder save',
                'meta' => [
                    'builder_blocks' => [[
                        'id' => 'module-hero-2',
                        'type' => 'section',
                        'settings' => [],
                    ]],
                    'builder_schema_version' => 1,
                ],
            ])
            ->assertOk();

        $this->assertSame(1, ContentRevision::query()->where('content_id', $content->id)->count());
        $revision = ContentRevision::query()->where('content_id', $content->id)->first();
        $this->assertNotNull($revision);
        $this->assertSame('section', $revision->meta['builder_blocks'][0]['type'] ?? null);
        $this->assertSame('module-hero', $revision->meta['builder_blocks'][0]['id'] ?? null);
    }

    public function test_content_lock_blocks_other_user(): void
    {
        $admin = $this->createUser(['email' => 'editor-one@example.com']);
        $other = $this->createUser(['email' => 'editor-two@example.com']);
        $admin->givePermissionTo('edit content');
        $other->givePermissionTo('edit content');
        $content = Content::factory()->create([
            'author_id' => $admin->id,
            'category_id' => null,
            'type' => 'page',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/publishing/contents/'.$content->id.'/lock')
            ->assertOk();

        $this->actingAs($other, 'sanctum')
            ->postJson('/api/v1/manage/publishing/contents/'.$content->id.'/lock')
            ->assertStatus(423);
    }
}
