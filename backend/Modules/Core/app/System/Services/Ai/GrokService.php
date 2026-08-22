<?php

declare(strict_types=1);

namespace Modules\Core\System\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Contracts\AiProviderInterface;
use Modules\Core\System\Models\Setting;

class GrokService implements AiProviderInterface
{
    protected ?string $apiKey;

    protected string $baseUrl = 'https://api.x.ai/v1';

    public function __construct(?string $apiKey = null)
    {
        $val = Setting::get('grok_api_key', '');
        $this->apiKey = $apiKey ?? (is_string($val) ? $val : '');
    }

    public function getName(): string
    {
        return 'xAI Grok';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            throw new \Exception('xAI Grok API Key is not configured.');
        }

        $val = Setting::get('grok_model', '');
        $model = $model !== '' ? $model : (is_string($val) && $val !== '' ? $val : 'grok-2-latest');

        try {
            $messages = [
                ['role' => 'system', 'content' => 'You are Grok, an intelligent, helpful, and direct content editor assistant.'],
            ];
            if ($context !== '') {
                $messages[] = ['role' => 'user', 'content' => "Context:\n{$context}"];
                $messages[] = ['role' => 'assistant', 'content' => 'I have received the context. How can I assist you with this content?'];
            }
            $messages[] = ['role' => 'user', 'content' => $prompt];

            $response = Http::timeout(45)->withToken($this->apiKey)
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
            Log::error('xAI Grok Generation Exception', ['message' => $e->getMessage()]);
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
                        $models[] = [
                            'id' => $id,
                            'name' => $id,
                        ];
                    }
                }
            }

            return count($models) > 0 ? $models : $this->getDefaultModels();
        } catch (\Exception $e) {
            Log::warning('xAI Grok GetModels Exception: '.$e->getMessage());

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

        Log::error('xAI Grok API Error', ['status' => $status, 'error' => $error]);

        throw match ($status) {
            401 => new \Exception('xAI Grok Authentication Failed: Invalid API Key.'),
            429 => new \Exception("xAI Grok Rate Limit or Credits Exhausted: {$error}"),
            default => new \Exception("xAI Grok API Error ({$status}): {$error}"),
        };
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    protected function getDefaultModels(): array
    {
        return [
            ['id' => 'grok-2-latest', 'name' => 'Grok 2 (Latest Frontier Model)'],
            ['id' => 'grok-2-vision-latest', 'name' => 'Grok 2 Vision (Multimodal)'],
            ['id' => 'grok-beta', 'name' => 'Grok Beta'],
        ];
    }
}
