<?php

declare(strict_types=1);

namespace Modules\CmsAi\Services;

use Modules\Core\System\Models\Setting;
use Modules\CmsAi\Contracts\AiProviderInterface;
use Modules\CmsAi\Services\Providers\DeepSeekService;
use Modules\CmsAi\Services\Providers\GeminiService;
use Modules\CmsAi\Services\Providers\OpenAiService;

class AiProviderFactory
{
    public static function make(?string $provider = null): AiProviderInterface
    {
        // If no provider specified, get default from settings
        if (! $provider) {
            $defaultProvider = Setting::get('ai_default_provider', 'gemini');
            $provider = is_string($defaultProvider) ? $defaultProvider : 'gemini';
        }

        return match ($provider) {
            'openai' => new OpenAiService,
            'deepseek' => new DeepSeekService,
            'gemini' => new GeminiService,
            default => new GeminiService,
        };
    }

    /**
     * Get list of all registered providers
     *
     * @return array<int, array{id: string, name: string, logo: string}>
     */
    public static function getProviders(): array
    {
        return [
            [
                'id' => 'gemini',
                'name' => 'Google Gemini',
                'logo' => 'https://www.gstatic.com/lamda/images/gemini_sparkle_v002_d4735304ff6292a690345.svg',
            ],
            [
                'id' => 'openai',
                'name' => 'OpenAI',
                'logo' => 'https://openai.com/favicon.ico',
            ],
            [
                'id' => 'deepseek',
                'name' => 'DeepSeek',
                'logo' => '',
            ],
        ];
    }
}
