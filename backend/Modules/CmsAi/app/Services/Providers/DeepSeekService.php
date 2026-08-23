<?php

namespace Modules\CmsAi\Services\Providers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\Setting;
use Modules\CmsAi\Contracts\AiProviderInterface;

class DeepSeekService implements AiProviderInterface
{
    protected ?string $apiKey;

    protected string $baseUrl = 'https://api.deepseek.com'; // DeepSeek Base URL

    public function __construct(?string $apiKey = null)
    {
        $val = Setting::get('deepseek_api_key', '');
        $this->apiKey = $apiKey ?? (is_string($val) ? $val : '');
    }

    public function getName(): string
    {
        return 'DeepSeek';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            throw new \Exception('DeepSeek API Key is not configured.');
        }

        $val = Setting::get('deepseek_model', '');
        $model = $model ?: (is_string($val) && $val !== '' ? $val : 'deepseek-chat');

        try {
            $messages = [
                ['role' => 'system', 'content' => 'You are a helpful content editor assistant.'],
                ['role' => 'user', 'content' => $context !== '' && $context !== '0' ? "Context:\n$context\n\nInstruction: $prompt" : $prompt],
            ];

            $response = Http::withToken($this->apiKey)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                $this->handleError($response);
            }

            /** @var array{choices: array<int, array{message: array{content: string}}>} $data */
            $data = $response->json();

            return $data['choices'][0]['message']['content'] ?? '';

        } catch (\Exception $e) {
            Log::error('DeepSeek Generation Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get available models
     *
     * @return array<int, array{id: string, name: string}>
     */
    public function getModels(): array
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            return [];
        }

        try {
            $response = Http::withToken($this->apiKey)->get("{$this->baseUrl}/models");

            if ($response->failed()) {
                return [];
            }

            /** @var array{data: array<int, array{id: string}>} $data */
            $data = $response->json();
            /** @var array<int, array{id: string, name: string}> $models */
            $models = [];

            foreach ($data['data'] as $m) {
                $models[] = [
                    'id' => strval($m['id']),
                    'name' => strval($m['id']),
                ];
            }

            return $models;

        } catch (\Exception $e) {
            Log::error('DeepSeek GetModels Exception', ['message' => $e->getMessage()]);

            return [];
        }
    }

    public function testConnection(): bool
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            throw new \Exception('API Key is missing.');
        }

        // DeepSeek also supports /models endpoint for check
        $response = Http::withToken($this->apiKey)->get("{$this->baseUrl}/models");

        if ($response->failed()) {
            $this->handleError($response);
        }

        return true;
    }

    /**
     * Handle API errors
     *
     * @param  Response  $response
     *
     * @throws \Exception
     */
    protected function handleError($response): never
    {
        $errorData = $response->json('error.message', 'Unknown error');
        $errorMsg = is_string($errorData) ? $errorData : 'Unknown error';

        if ($response->status() === 401) {
            throw new \Exception('Invalid DeepSeek API Key.');
        }

        if ($response->status() === 402) {
            throw new \Exception('DeepSeek Validation Failed (Insufficient Balance).');
        }

        throw new \Exception('DeepSeek API Error: '.$errorMsg);
    }
}
