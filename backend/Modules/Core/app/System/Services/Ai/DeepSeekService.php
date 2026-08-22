<?php

declare(strict_types=1);

namespace Modules\Core\System\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Contracts\AiProviderInterface;
use Modules\Core\System\Models\Setting;

class DeepSeekService implements AiProviderInterface
{
    protected ?string $apiKey;

    protected string $baseUrl = 'https://api.deepseek.com/v1';

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
        $model = $model !== '' ? $model : (is_string($val) && $val !== '' ? $val : 'deepseek-chat');

        try {
            $messages = [
                ['role' => 'system', 'content' => 'You are a helpful content editor assistant.'],
            ];
            if ($context !== '') {
                $messages[] = ['role' => 'user', 'content' => "Context:\n{$context}"];
                $messages[] = ['role' => 'assistant', 'content' => 'I have received the context. How can I help you?'];
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

            $data = AiHttpResponse::jsonArray($response);

            return AiHttpResponse::chatCompletionContent($data);
        } catch (\Exception $e) {
            Log::error('DeepSeek Generation Exception', ['message' => $e->getMessage()]);
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

            $data = AiHttpResponse::jsonArray($response);
            $models = [];

            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $m) {
                    if (is_array($m) && isset($m['id']) && is_string($m['id'])) {
                        $id = $m['id'];
                        $models[] = [
                            'id' => $id,
                            'name' => $id === 'deepseek-chat' ? 'DeepSeek V3 (Chat)' : ($id === 'deepseek-reasoner' ? 'DeepSeek R1 (Reasoner)' : $id),
                        ];
                    }
                }
            }

            return count($models) > 0 ? $models : $this->getDefaultModels();
        } catch (\Exception $e) {
            Log::warning('DeepSeek GetModels Exception: '.$e->getMessage());

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
        $error = AiHttpResponse::errorMessage($response);

        Log::error('DeepSeek API Error', ['status' => $status, 'error' => $error]);

        throw match ($status) {
            401 => new \Exception('DeepSeek Authentication Failed: Invalid API Key.'),
            429 => new \Exception("DeepSeek Balance Insufficient or Rate Limit: {$error}"),
            default => new \Exception("DeepSeek API Error ({$status}): {$error}"),
        };
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    protected function getDefaultModels(): array
    {
        return [
            ['id' => 'deepseek-chat', 'name' => 'DeepSeek V3 (Chat - High Speed)'],
            ['id' => 'deepseek-reasoner', 'name' => 'DeepSeek R1 (Deep Reasoning)'],
        ];
    }
}
