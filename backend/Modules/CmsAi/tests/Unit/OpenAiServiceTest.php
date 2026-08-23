<?php

declare(strict_types=1);

namespace Modules\CmsAi\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Modules\CmsAi\Services\Providers\OpenAiService;
use Tests\TestCase;

class OpenAiServiceTest extends TestCase
{
    protected OpenAiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OpenAiService('test-api-key');
    }

    public function test_returns_correct_provider_name(): void
    {
        $this->assertEquals('OpenAI', $this->service->getName());
    }

    public function test_throws_exception_if_api_key_is_missing_during_generation(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAI API Key is not configured.');

        $service = new OpenAiService('');
        $service->generateText('Test prompt');
    }

    public function test_tests_connection_successfully_with_valid_response(): void
    {
        Http::fake([
            'api.openai.com/v1/models' => Http::response(['data' => []], 200),
        ]);

        $this->assertTrue($this->service->testConnection());
    }

    public function test_throws_exception_on_connection_test_failure(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid OpenAI API Key.');

        Http::fake([
            'api.openai.com/v1/models' => Http::response(['error' => ['message' => 'Invalid key']], 401),
        ]);

        $this->service->testConnection();
    }

    public function test_generates_text_successfully(): void
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => ['content' => 'Generated content here.'],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generateText('Write a poem');
        $this->assertEquals('Generated content here.', $result);
    }

    public function test_throws_exception_on_api_failure_quota(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAI Rate Limit / Quota Exceeded.');

        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'error' => ['message' => 'Rate limit exceeded'],
            ], 429),
        ]);

        $this->service->generateText('Hello');
    }

    public function test_gets_models_successfully(): void
    {
        Http::fake([
            'api.openai.com/v1/models' => Http::response([
                'data' => [
                    ['id' => 'gpt-4'],
                    ['id' => 'gpt-3.5-turbo'],
                ],
            ], 200),
        ]);

        $models = $this->service->getModels();
        $this->assertCount(2, $models);
        // Sorted alphabetically so gpt-3.5-turbo is first
        $this->assertEquals('gpt-3.5-turbo', $models[0]['id']);
        $this->assertEquals('gpt-4', $models[1]['id']);
    }

    public function test_get_models_returns_empty_when_no_api_key(): void
    {
        $service = new OpenAiService('');
        $this->assertEmpty($service->getModels());
    }

    public function test_get_models_returns_empty_on_failure(): void
    {
        Http::fake([
            'api.openai.com/v1/models' => Http::response([], 500),
        ]);

        $this->assertEmpty($this->service->getModels());
    }
}
