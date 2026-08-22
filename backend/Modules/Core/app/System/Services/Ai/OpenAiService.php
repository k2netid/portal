<?php

declare(strict_types=1);

namespace Modules\Core\System\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Contracts\AiProviderInterface;
use Modules\Core\System\Models\Setting;

class OpenAiService implements AiProviderInterface
{
    protected ?string $apiKey;

    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct(?string $apiKey = null)
    {
        $val = Setting::get('openai_api_key', '');
        $this->apiKey = $apiKey ?? (is_string($val) ? $val : '');
    }

    public function getName(): string
    {
        return 'OpenAI';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            throw new \Exception('OpenAI API Key is not configured.');
        }

        $val = Setting::get('openai_model', '');
        $model = $model !== '' ? $model : (is_string($val) && $val !== '' ? $val : 'gpt-4o-mini');

        try {
            $messages = [
                ['role' => 'system', 'content' => 'You are a helpful content editor assistant.'],
            ];
            if ($context !== '') {
                $messages[] = ['role' => 'user', 'content' => "Context:\n{$context}"];
                $messages[] = ['role' => 'assistant', 'content' => 'I have received the context. How can I help you?'];
            }
            $messages[] = ['role' => 'user', 'content' => $prompt];

            $response = Http::timeout(30)->withToken($this->apiKey)
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
            Log::error('OpenAI Generation Exception', ['message' => $e->getMessage()]);
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
                        if (str_starts_with($id, 'gpt-') || str_starts_with($id, 'o1') || str_starts_with($id, 'o3')) {
                            $models[] = [
                                'id' => $id,
                                'name' => $id,
                            ];
                        }
                    }
                }
            }

            usort($models, fn (array $a, array $b): int => strcmp((string) $a['id'], (string) $b['id']));

            return count($models) > 0 ? $models : $this->getDefaultModels();
        } catch (\Exception $e) {
            Log::warning('OpenAI GetModels Exception: '.$e->getMessage());

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

        Log::error('OpenAI API Error', ['status' => $status, 'error' => $error]);

        throw match ($status) {
            401 => new \Exception('OpenAI Authentication Failed: Invalid API Key.'),
            429 => new \Exception("OpenAI Rate Limit Exceeded or Quota Exhausted: {$error}"),
            default => new \Exception("OpenAI API Error ({$status}): {$error}"),
        };
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    protected function getDefaultModels(): array
    {
        return [
            ['id' => 'gpt-4o-mini', 'name' => 'GPT-4o Mini (Fast & Cost Effective)'],
            ['id' => 'gpt-4o', 'name' => 'GPT-4o (Omni Flagship)'],
            ['id' => 'gpt-4-turbo', 'name' => 'GPT-4 Turbo'],
            ['id' => 'gpt-3.5-turbo', 'name' => 'GPT-3.5 Turbo'],
        ];
    }
}
