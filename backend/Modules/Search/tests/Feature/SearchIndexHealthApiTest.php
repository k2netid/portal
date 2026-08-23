<?php

declare(strict_types=1);

namespace Modules\Search\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Search\Tests\SearchTestCase;

class SearchIndexHealthApiTest extends SearchTestCase
{
    use RefreshDatabase;


    public function test_admin_can_fetch_index_health_snapshot(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/search/index-health');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'in_sync',
                    'total_lag',
                    'checked_at',
                    'resources' => [
                        ['key', 'label', 'source', 'indexed', 'lag'],
                    ],
                    'index_totals' => ['all', 'post', 'page', 'category', 'tag'],
                ],
            ]);
    }
}
