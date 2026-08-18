<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Layout\Models\Theme;
use Modules\Content\Publishing\Models\Content;
use Tests\TestCase;

class OnboardingStatusApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_can_fetch_onboarding_status(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/onboarding-status');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'dismissed',
                    'steps' => ['identity', 'theme', 'first_page'],
                    'progress_percent',
                    'complete',
                ],
            ]);
    }

    public function test_dismiss_onboarding_persists_preference(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/onboarding/dismiss')
            ->assertOk()
            ->assertJsonPath('data.dismissed', true);

        $admin->refresh();
        $this->assertTrue((bool) $admin->getPreference('onboarding.dismissed', false));
    }

    public function test_onboarding_complete_when_theme_and_page_exist(): void
    {
        $admin = $this->createAdminUser();
        Theme::query()->update(['is_active' => false]);
        Theme::factory()->create(['slug' => 'janari', 'is_active' => true, 'type' => 'frontend']);
        Content::factory()->create([
            'type' => 'page',
            'status' => 'published',
            'author_id' => $admin->id,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/onboarding-status');

        $response->assertOk()
            ->assertJsonPath('data.steps.theme', true)
            ->assertJsonPath('data.steps.first_page', true);
    }
}
