<?php

declare(strict_types=1);

namespace Modules\Core\System\Services\Ai;

use Modules\Core\System\Contracts\AiProviderInterface;
use Modules\Core\System\Models\Setting;

class AiProviderFactory
{
    public static function make(?string $provider = null, ?string $apiKey = null): AiProviderInterface
    {
        if (! $provider) {
            $defaultProvider = Setting::get('ai_default_provider', 'gemini');
            $provider = is_string($defaultProvider) && $defaultProvider !== '' ? $defaultProvider : 'gemini';
        }

        return match ($provider) {
            'openai' => new OpenAiService($apiKey),
            'claude' => new ClaudeService($apiKey),
            'deepseek' => new DeepSeekService($apiKey),
            'grok' => new GrokService($apiKey),
            'openrouter' => new OpenRouterService($apiKey),
            'gemini' => new GeminiService($apiKey),
            default => new GeminiService($apiKey),
        };
    }

    /**
     * Get list of all registered providers
     *
     * @return array<int, array{id: string, name: string, description: string, logo: string, docsUrl: string}>
     */
    public static function getProviders(): array
    {
        return [
            [
                'id' => 'gemini',
                'name' => 'Google Gemini',
                'description' => 'Google next-generation multimodal AI with high speed & generous free tier.',
                'logo' => '/assets/ai/gemini.svg',
                'docsUrl' => 'https://aistudio.google.com/app/apikey',
            ],
            [
                'id' => 'openai',
                'name' => 'OpenAI',
                'description' => 'Industry standard models including GPT-4o, GPT-4o Mini, and o1/o3 reasoning.',
                'logo' => '/assets/ai/openai.svg',
                'docsUrl' => 'https://platform.openai.com/api-keys',
            ],
            [
                'id' => 'claude',
                'name' => 'Anthropic Claude',
                'description' => 'Top-tier writing, coding, and nuanced contextual reasoning with Claude 3.5 Sonnet & Haiku.',
                'logo' => '/assets/ai/claude.svg',
                'docsUrl' => 'https://console.anthropic.com/settings/keys',
            ],
            [
                'id' => 'deepseek',
                'name' => 'DeepSeek',
                'description' => 'State-of-the-art open-weights model with extremely high performance and low cost.',
                'logo' => '/assets/ai/deepseek.svg',
                'docsUrl' => 'https://platform.deepseek.com/api_keys',
            ],
            [
                'id' => 'grok',
                'name' => 'xAI Grok',
                'description' => 'Direct, witty, and real-time knowledge intelligence powered by Elon Musk xAI.',
                'logo' => '/assets/ai/grok.svg',
                'docsUrl' => 'https://console.x.ai/',
            ],
            [
                'id' => 'openrouter',
                'name' => 'OpenRouter',
                'description' => 'Unified single API key giving access to over 200+ models from all top AI labs.',
                'logo' => '/assets/ai/openrouter.svg',
                'docsUrl' => 'https://openrouter.ai/keys',
            ],
        ];
    }
}
