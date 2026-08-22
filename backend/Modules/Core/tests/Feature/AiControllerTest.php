<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Modules\Core\System\Models\User;
use Tests\TestCase;

final class AiControllerTest extends TestCase
{
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::first() ?? User::factory()->create();
    }

    public function test_get_providers_returns_all_six_providers(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/manage/ai/providers');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $providers = $response->json('data');
        $this->assertIsArray($providers);
        $this->assertCount(6, $providers);

        $ids = array_column($providers, 'id');
        $this->assertContains('gemini', $ids);
        $this->assertContains('openai', $ids);
        $this->assertContains('claude', $ids);
        $this->assertContains('deepseek', $ids);
        $this->assertContains('grok', $ids);
        $this->assertContains('openrouter', $ids);
    }

    public function test_get_models_returns_default_models_for_providers(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/manage/ai/models/gemini');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $models = $response->json('data');
        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
    }

    public function test_test_connection_with_mocked_http(): void
    {
        Http::fake([
            'https://api.openai.com/v1/models' => Http::response([
                'data' => [
                    ['id' => 'gpt-4o'],
                    ['id' => 'gpt-4o-mini'],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/v1/manage/ai/test', [
                'provider' => 'openai',
                'api_key' => 'sk-test-key-12345',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_generate_with_mocked_http(): void
    {
        \Modules\Core\System\Models\Setting::set('gemini_api_key', 'AIzaSyTestMockKey123', 'password', 'ai');

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'This is a test generated AI response.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/v1/manage/ai/generate', [
                'provider' => 'gemini',
                'prompt' => 'Write a short intro',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.content', 'This is a test generated AI response.');
    }

    public function test_get_models_for_all_providers(): void
    {
        $providers = ['gemini', 'openai', 'claude', 'deepseek', 'grok', 'openrouter'];
        foreach ($providers as $provider) {
            $response = $this->actingAs($this->adminUser, 'sanctum')
                ->getJson("/api/v1/manage/ai/models/{$provider}");

            $response->assertOk()
                ->assertJsonPath('success', true);

            $models = $response->json('data');
            $this->assertIsArray($models);
            $this->assertNotEmpty($models, "Provider {$provider} should return models");
        }
    }

    public function test_test_connection_failure_handling(): void
    {
        Http::fake([
            'https://api.openai.com/v1/models' => Http::response([
                'error' => ['message' => 'Incorrect API key provided'],
            ], 401),
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/v1/manage/ai/test', [
                'provider' => 'openai',
                'api_key' => 'invalid-key',
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }
}
