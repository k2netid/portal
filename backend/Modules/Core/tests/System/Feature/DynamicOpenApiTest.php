<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\ContentType;
use Modules\Core\System\Support\DynamicOpenApiBuilder;
use Tests\TestCase;

class DynamicOpenApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_openapi_by_slug_returns_paths_for_dynamic_api(): void
    {
        $admin = $this->createAdminUser();

        ContentType::create([
            'name' => 'Announcements',
            'slug' => 'announcements',
            'is_active' => true,
            'fields' => [
                ['name' => 'Title', 'slug' => 'title', 'type' => 'text', 'is_required' => true],
                ['name' => 'Published', 'slug' => 'published', 'type' => 'boolean', 'is_required' => false],
            ],
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/cck/types/by-slug/announcements/openapi');

        $response->assertStatus(200)
            ->assertJsonPath('data.openapi', '3.0.3')
            ->assertJsonPath('data.paths./api/v1/dynamic/announcements.get.operationId', 'dynamic.announcements.index')
            ->assertJsonPath('data.components.schemas.announcementsRecordInput.properties.title.type', 'string')
            ->assertJsonPath('data.x-cck.slug', 'announcements');
    }

    public function test_openapi_index_lists_active_slugs(): void
    {
        $admin = $this->createAdminUser();

        ContentType::create([
            'name' => 'Products',
            'slug' => 'products',
            'is_active' => true,
            'fields' => [],
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/cck/types/openapi-index')
            ->assertStatus(200)
            ->assertJsonFragment(['slug' => 'products']);
    }

    public function test_builder_includes_select_enum(): void
    {
        $type = ContentType::create([
            'name' => 'Leads',
            'slug' => 'leads',
            'is_active' => true,
            'fields' => [
                [
                    'name' => 'Status',
                    'slug' => 'status',
                    'type' => 'select',
                    'options' => ['new', 'won'],
                    'is_required' => true,
                ],
            ],
        ]);

        $doc = (new DynamicOpenApiBuilder)->buildFor($type);

        $this->assertSame(
            ['new', 'won'],
            $doc['components']['schemas']['leadsRecordInput']['properties']['status']['enum'] ?? null,
        );
    }
}
