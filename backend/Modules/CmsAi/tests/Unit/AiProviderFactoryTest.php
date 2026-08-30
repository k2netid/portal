<?php

declare(strict_types=1);

namespace Modules\CmsAi\Tests\Unit;

use Modules\CmsAi\Services\AiProviderFactory;
use Modules\CmsAi\Services\Providers\DeepSeekService;
use Modules\CmsAi\Services\Providers\GeminiService;
use Modules\CmsAi\Services\Providers\OpenAiService;
use Tests\TestCase;

class AiProviderFactoryTest extends TestCase
{
    public function test_returns_a_list_of_providers(): void
    {
        $providers = AiProviderFactory::getProviders();
        $this->assertIsArray($providers);
        $this->assertCount(3, $providers);

        $ids = array_column($providers, 'id');
        $this->assertContains('openai', $ids);
        $this->assertContains('gemini', $ids);
        $this->assertContains('deepseek', $ids);
    }

    public function test_resolves_correct_provider_instance_when_explicitly_specified(): void
    {
        $openai = AiProviderFactory::make('openai');
        $this->assertInstanceOf(OpenAiService::class, $openai);

        $gemini = AiProviderFactory::make('gemini');
        $this->assertInstanceOf(GeminiService::class, $gemini);

        $deepseek = AiProviderFactory::make('deepseek');
        $this->assertInstanceOf(DeepSeekService::class, $deepseek);
    }

    public function test_falls_back_to_gemini_when_an_unknown_provider_is_specified(): void
    {
        $unknown = AiProviderFactory::make('unknown_provider');
        $this->assertInstanceOf(GeminiService::class, $unknown);
    }
}
