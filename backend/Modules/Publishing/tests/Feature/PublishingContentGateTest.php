<?php

declare(strict_types=1);

namespace Modules\Publishing\Tests\Feature;

use Modules\Publishing\Models\Content;
use Tests\TestCase;

class PublishingContentGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_manage_contents_forbidden_when_pack_inactive(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/publishing/contents')
            ->assertForbidden();
    }

    public function test_public_contents_lists_published_when_pack_active(): void
    {
        $this->activatePack('publishing');
        $admin = $this->createAdminUser();
        $content = Content::factory()->published()->create([
            'author_id' => $admin->id,
            'category_id' => null,
            'type' => 'post',
            'title' => 'Published pack post',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/publishing/contents')
            ->assertOk();

        $this->getJson('/api/v1/public/publishing/contents')
            ->assertOk()
            ->assertJsonFragment(['slug' => $content->slug]);
    }
}
