<?php

declare(strict_types=1);

namespace Modules\Core\System\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Contracts\AiProviderInterface;
use Modules\Core\System\Models\Setting;

class OpenRouterService implements AiProviderInterface
{
    protected ?string $apiKey;

    protected string $baseUrl = 'https://openrouter.ai/api/v1';

    public function __construct(?string $apiKey = null)
    {
        $val = Setting::get('openrouter_api_key', '');
        $this->apiKey = $apiKey ?? (is_string($val) ? $val : '');
    }

    public function getName(): string
    {
        return 'OpenRouter';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            throw new \Exception('OpenRouter API Key is not configured.');
        }

        $val = Setting::get('openrouter_model', '');
        $model = $model !== '' ? $model : (is_string($val) && $val !== '' ? $val : 'openrouter/auto');

        try {
            $messages = [
                ['role' => 'system', 'content' => 'You are a helpful content editor assistant.'],
            ];
            if ($context !== '') {
                $messages[] = ['role' => 'user', 'content' => "Context:\n{$context}"];
                $messages[] = ['role' => 'assistant', 'content' => 'I have received the context. How can I assist you with this content?'];
            }
            $messages[] = ['role' => 'user', 'content' => $prompt];

            $siteUrl = is_string(config('app.url')) ? config('app.url') : 'http://localhost';

            $response = Http::timeout(45)
                ->withToken($this->apiKey)
                ->withHeaders([
                    'HTTP-Referer' => $siteUrl,
                    'X-Title' => 'Jejakawan Engine',
                ])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                $this->handleError($response);
            }

            $data = $response->json();

            return $data['choices'][0]['message']['content'] ?? '';
        } catch (\Exception $e) {
            Log::error('OpenRouter Generation Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function getModels(): array
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            return $this->getDefaultModels();
        }

        try {
            $response = Http::timeout(15)->withToken($this->apiKey)->get("{$this->baseUrl}/models");

            if ($response->failed()) {
                return $this->getDefaultModels();
            }

            $data = $response->json();
            $models = [];

            if (is_array($data) && isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $m) {
                    if (isset($m['id']) && is_string($m['id'])) {
                        $id = $m['id'];
                        $name = isset($m['name']) && is_string($m['name']) ? $m['name'] : $id;
                        $models[] = [
                            'id' => $id,
                            'name' => "{$name} ({$id})",
                        ];
                    }
                }
            }

            return count($models) > 0 ? $models : $this->getDefaultModels();
        } catch (\Exception $e) {
            Log::warning('OpenRouter GetModels Exception: '.$e->getMessage());

            return $this->getDefaultModels();
        }
    }

    public function testConnection(): bool
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            throw new \Exception('API Key is missing.');
        }

        $response = Http::timeout(15)->withToken($this->apiKey)->get("{$this->baseUrl}/models");

        if ($response->failed()) {
            $this->handleError($response);
        }

        return true;
    }

    protected function handleError(Response $response): void
    {
        $status = $response->status();
        $error = $response->json('error.message') ?? $response->body();

        Log::error('OpenRouter API Error', ['status' => $status, 'error' => $error]);

        throw match ($status) {
            401 => new \Exception('OpenRouter Authentication Failed: Invalid API Key.'),
            429 => new \Exception("OpenRouter Rate Limit or Credits Exhausted: {$error}"),
            default => new \Exception("OpenRouter API Error ({$status}): {$error}"),
        };
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    protected function getDefaultModels(): array
    {
        return [
            ['id' => 'openrouter/auto', 'name' => 'OpenRouter Auto (Best Available Model)'],
            ['id' => 'anthropic/claude-3.5-sonnet', 'name' => 'Anthropic: Claude 3.5 Sonnet'],
            ['id' => 'openai/gpt-4o', 'name' => 'OpenAI: GPT-4o'],
            ['id' => 'deepseek/deepseek-chat', 'name' => 'DeepSeek: V3 (Chat)'],
            ['id' => 'deepseek/deepseek-r1', 'name' => 'DeepSeek: R1 (Reasoner)'],
            ['id' => 'meta-llama/llama-3.3-70b-instruct', 'name' => 'Meta: Llama 3.3 70B Instruct'],
            ['id' => 'google/gemini-2.0-flash-exp:free', 'name' => 'Google: Gemini 2.0 Flash (Free)'],
        ];
    }
}
