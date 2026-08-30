<?php

declare(strict_types=1);

namespace Modules\CmsAi\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Modules\CmsAi\Services\Providers\GeminiService;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    protected GeminiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeminiService('test-api-key');
    }

    public function test_returns_correct_provider_name(): void
    {
        $this->assertEquals('Google Gemini', $this->service->getName());
    }

    public function test_throws_exception_if_api_key_is_missing_during_generation(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Gemini API Key is not configured.');

        $service = new GeminiService('');
        $service->generateText('Test prompt');
    }

    public function test_generates_text_successfully(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Generated gemini content.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generateText('Hello Gemini');
        $this->assertEquals('Generated gemini content.', $result);
    }

    public function test_throws_exception_on_unexpected_response_format(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unexpected response format from Gemini.');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'invalid' => 'format',
            ], 200),
        ]);

        $this->service->generateText('Hello');
    }

    public function test_throws_exception_on_api_failure_quota(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Gemini Quota Exceeded. Please check billing.');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Quota Exceeded'],
            ], 429),
        ]);

        $this->service->generateText('Hello');
    }

    public function test_throws_exception_on_general_api_failure(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Gemini API Error: Invalid Request');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Invalid Request'],
            ], 400),
        ]);

        $this->service->generateText('Hello');
    }

    public function test_gets_models_successfully(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*/models' => Http::response([
                'models' => [
                    [
                        'name' => 'models/gemini-pro',
                        'displayName' => 'Gemini Pro',
                        'supportedGenerationMethods' => ['generateContent'],
                    ],
                    [
                        'name' => 'models/unsupported',
                        'supportedGenerationMethods' => ['otherMethod'],
                    ],
                ],
            ], 200),
        ]);

        $models = $this->service->getModels();
        $this->assertCount(1, $models);
        $this->assertEquals('gemini-pro', $models[0]['id']);
        $this->assertEquals('Gemini Pro', $models[0]['name']);
    }

    public function test_get_models_returns_empty_when_no_api_key(): void
    {
        $service = new GeminiService('');
        $this->assertEmpty($service->getModels());
    }

    public function test_get_models_returns_empty_on_failure(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*/models' => Http::response([], 500),
        ]);

        $this->assertEmpty($this->service->getModels());
    }

    public function test_tests_connection_successfully(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*/models' => Http::response(['models' => []], 200),
        ]);

        $this->assertTrue($this->service->testConnection());
    }

    public function test_test_connection_throws_when_no_key(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API Key is missing.');

        $service = new GeminiService('');
        $service->testConnection();
    }
}
