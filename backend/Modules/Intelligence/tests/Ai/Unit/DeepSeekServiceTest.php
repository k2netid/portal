<?php

declare(strict_types=1);

namespace Modules\Intelligence\Ai\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Modules\Intelligence\Ai\Services\Providers\DeepSeekService;
use Tests\TestCase;

class DeepSeekServiceTest extends TestCase
{
    protected DeepSeekService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DeepSeekService('test-api-key');
    }

    public function test_returns_correct_provider_name(): void
    {
        $this->assertEquals('DeepSeek', $this->service->getName());
    }

    public function test_throws_exception_if_api_key_is_missing_during_generation(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('DeepSeek API Key is not configured.');

        $service = new DeepSeekService('');
        $service->generateText('Test prompt');
    }

    public function test_generates_text_successfully(): void
    {
        Http::fake([
            'api.deepseek.com/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => ['content' => 'Generated deepseek content.'],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generateText('Hello DeepSeek');
        $this->assertEquals('Generated deepseek content.', $result);
    }

    public function test_throws_exception_on_api_failure_balance(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('DeepSeek Validation Failed (Insufficient Balance).');

        Http::fake([
            'api.deepseek.com/chat/completions' => Http::response([
                'error' => ['message' => 'Insufficient Balance'],
            ], 402),
        ]);

        $this->service->generateText('Hello');
    }

    public function test_throws_exception_on_general_api_failure(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('DeepSeek API Error: Unknown error');

        Http::fake([
            'api.deepseek.com/chat/completions' => Http::response([
                'error' => [],
            ], 400),
        ]);

        $this->service->generateText('Hello');
    }

    public function test_gets_models_successfully(): void
    {
        Http::fake([
            'api.deepseek.com/models' => Http::response([
                'data' => [
                    [
                        'id' => 'deepseek-chat',
                    ],
                    [
                        'id' => 'deepseek-coder',
                    ],
                ],
            ], 200),
        ]);

        $models = $this->service->getModels();
        $this->assertCount(2, $models);
        $this->assertEquals('deepseek-chat', $models[0]['id']);
        $this->assertEquals('deepseek-coder', $models[1]['id']);
    }

    public function test_get_models_returns_empty_when_no_api_key(): void
    {
        $service = new DeepSeekService('');
        $this->assertEmpty($service->getModels());
    }

    public function test_get_models_returns_empty_on_failure(): void
    {
        Http::fake([
            'api.deepseek.com/models' => Http::response([], 500),
        ]);

        $this->assertEmpty($this->service->getModels());
    }

    public function test_tests_connection_successfully(): void
    {
        Http::fake([
            'api.deepseek.com/models' => Http::response(['data' => []], 200),
        ]);

        $this->assertTrue($this->service->testConnection());
    }

    public function test_test_connection_throws_when_no_key(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API Key is missing.');

        $service = new DeepSeekService('');
        $service->testConnection();
    }

    public function test_test_connection_throws_when_invalid_key(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid DeepSeek API Key.');

        Http::fake([
            'api.deepseek.com/models' => Http::response(['error' => ['message' => 'Invalid key']], 401),
        ]);

        $this->service->testConnection();
    }
}
