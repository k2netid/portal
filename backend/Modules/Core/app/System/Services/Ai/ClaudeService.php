<?php

declare(strict_types=1);

namespace Modules\Core\System\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Contracts\AiProviderInterface;
use Modules\Core\System\Models\Setting;

class ClaudeService implements AiProviderInterface
{
    protected ?string $apiKey;

    protected string $baseUrl = 'https://api.anthropic.com/v1';

    protected string $apiVersion = '2023-06-01';

    public function __construct(?string $apiKey = null)
    {
        $val = Setting::get('claude_api_key', '');
        $this->apiKey = $apiKey ?? (is_string($val) ? $val : '');
    }

    public function getName(): string
    {
        return 'Anthropic Claude';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            throw new \Exception('Anthropic Claude API Key is not configured.');
        }

        $val = Setting::get('claude_model', '');
        $model = $model !== '' ? $model : (is_string($val) && $val !== '' ? $val : 'claude-3-5-sonnet-20241022');

        try {
            $messages = [];
            if ($context !== '') {
                $messages[] = ['role' => 'user', 'content' => "Context:\n{$context}"];
                $messages[] = ['role' => 'assistant', 'content' => 'I understand the context. How can I assist you with this content?'];
            }
            $messages[] = ['role' => 'user', 'content' => $prompt];

            $response = Http::timeout(45)->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->apiVersion,
                'content-type' => 'application/json',
            ])->post("{$this->baseUrl}/messages", [
                'model' => $model,
                'max_tokens' => 4096,
                'system' => 'You are a helpful and articulate content editor assistant. Provide high quality, well structured content.',
                'messages' => $messages,
            ]);

            if ($response->failed()) {
                $this->handleError($response);
            }

            $data = AiHttpResponse::jsonArray($response);

            return AiHttpResponse::claudeTextContent($data);
        } catch (\Exception $e) {
            Log::error('Claude Generation Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function getModels(): array
    {
        return $this->getDefaultModels();
    }

    public function testConnection(): bool
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            throw new \Exception('API Key is missing.');
        }

        $response = Http::timeout(15)->withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
            'content-type' => 'application/json',
        ])->post("{$this->baseUrl}/messages", [
            'model' => 'claude-3-5-haiku-20241022',
            'max_tokens' => 1,
            'messages' => [
                ['role' => 'user', 'content' => 'ping'],
            ],
        ]);

        if ($response->failed()) {
            $this->handleError($response);
        }

        return true;
    }

    protected function handleError(Response $response): void
    {
        $status = $response->status();
        $error = AiHttpResponse::errorMessage($response);

        Log::error('Anthropic Claude API Error', ['status' => $status, 'error' => $error]);

        throw match ($status) {
            401 => new \Exception('Anthropic Claude Authentication Failed: Invalid API Key.'),
            429 => new \Exception("Anthropic Claude Rate Limit or Credit Exhausted: {$error}"),
            default => new \Exception("Anthropic Claude API Error ({$status}): {$error}"),
        };
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    protected function getDefaultModels(): array
    {
        return [
            ['id' => 'claude-3-5-sonnet-20241022', 'name' => 'Claude 3.5 Sonnet (State-of-the-art)'],
            ['id' => 'claude-3-5-haiku-20241022', 'name' => 'Claude 3.5 Haiku (Ultra Fast & Lightweight)'],
            ['id' => 'claude-3-opus-20240229', 'name' => 'Claude 3 Opus (Complex Analysis)'],
        ];
    }
}
