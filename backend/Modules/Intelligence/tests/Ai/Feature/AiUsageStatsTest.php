<?php

declare(strict_types=1);

namespace Modules\Intelligence\Ai\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Intelligence\Ai\Models\AiUsageLog;
use Tests\TestCase;

class AiUsageStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_authenticated_user_can_fetch_usage_stats(): void
    {
        $admin = $this->createAdminUser();

        AiUsageLog::query()->create([
            'feature' => 'draft_publishing',
            'provider' => 'openai',
            'tokens_in' => 10,
            'tokens_out' => 100,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/ai/usage-stats');

        $response->assertOk()
            ->assertJsonPath('data.total_calls', 1)
            ->assertJsonPath('data.period_days', 30);
    }
}
