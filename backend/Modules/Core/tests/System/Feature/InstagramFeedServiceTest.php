<?php

declare(strict_types=1);

namespace Modules\Core\Tests\System\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\InstagramFeedService;
use Tests\TestCase;

class InstagramFeedServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_feed_returns_disabled_when_plugin_inactive(): void
    {
        $response = $this->getJson('/api/v1/public/social-feed/instagram');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.reason', 'inactive')
            ->assertJsonPath('data.items', []);
    }

    public function test_activation_gatekeeper_rejects_empty_credentials(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->givePermissionTo('manage system');

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/instagram-feed/activate');

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.code', 'missing_required_settings');
    }

    public function test_test_connection_endpoint_succeeds_with_mock_token(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->givePermissionTo('manage system');

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/instagram/test-connection', [
                'access_token' => 'mock_valid_token_123',
                'instagram_username' => 'testuser',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.username', 'testuser')
            ->assertJsonPath('data.account_type', 'BUSINESS');
    }

    public function test_public_feed_returns_mock_feed_when_active_with_mock_token(): void
    {
        /** @var Extension $extension */
        $extension = Extension::where('slug', 'instagram-feed')->firstOrFail();
        $extension->update([
            'status' => 'active',
            'is_active' => true,
            'settings' => [
                'access_token' => 'mock_valid_token_123',
                'instagram_username' => 'myschool',
                'theme_blocks' => [['slot' => 'after_hero']],
                'post_limit' => 6,
                'show_likes_count' => true,
                'show_comments_count' => true,
            ],
        ]);

        $response = $this->getJson('/api/v1/public/social-feed/instagram');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.username', 'myschool');

        $items = $response->json('data.items');
        $this->assertIsArray($items);
        $this->assertCount(6, $items);
        $this->assertArrayHasKey('permalink', $items[0]);
        $this->assertArrayHasKey('like_count', $items[0]);
    }
}
