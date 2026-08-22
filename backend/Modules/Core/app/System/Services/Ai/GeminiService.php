<?php

declare(strict_types=1);

namespace Modules\Core\System\Services\Ai;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Contracts\AiProviderInterface;
use Modules\Core\System\Models\Setting;

class GeminiService implements AiProviderInterface
{
    protected ?string $apiKey;

    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(?string $apiKey = null)
    {
        $settingKey = Setting::get('gemini_api_key');
        $defaultKey = is_string(config('services.gemini.api_key', '')) ? config('services.gemini.api_key', '') : '';
        $this->apiKey = $apiKey ?? (is_string($settingKey) ? $settingKey : $defaultKey);
    }

    public function getName(): string
    {
        return 'Google Gemini';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            throw new \Exception('Gemini API Key is not configured.');
        }

        $settingModel = Setting::get('gemini_model', '');
        $model = $model !== '' ? $model : (is_string($settingModel) && $settingModel !== '' ? $settingModel : 'gemini-2.0-flash');
        $modelStr = str_replace('models/', '', $model);

        try {
            $contents = [];
            if ($context !== '') {
                $contents[] = [
                    'role' => 'user',
                    'parts' => [['text' => "Context:\n{$context}"]],
                ];
                $contents[] = [
                    'role' => 'model',
                    'parts' => [['text' => 'Understood. I will use this context for the upcoming instructions.']],
                ];
            }
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $prompt]],
            ];

            $response = $this->withApiKeyHeaders()->post("{$this->baseUrl}/models/{$modelStr}:generateContent", [
                'contents' => $contents,
                'system_instruction' => [
                    'parts' => [
                        ['text' => 'You are a helpful content editor assistant. Help with content creation, editing, and optimization.'],
                    ],
                ],
            ]);

            if ($response->failed()) {
                $this->handleError($response);
            }

            $data = $response->json();

            if (is_array($data) && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $textRaw = $data['candidates'][0]['content']['parts'][0]['text'];

                return is_string($textRaw) ? $textRaw : (string) $textRaw;
            }

            throw new \Exception('Unexpected response format from Gemini.');
        } catch (\Exception $e) {
            Log::error('Gemini Generation Exception', ['message' => $e->getMessage()]);
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
            $response = $this->withApiKeyHeaders()->get("{$this->baseUrl}/models");

            if ($response->failed()) {
                return $this->getDefaultModels();
            }

            $data = $response->json();
            $models = [];

            if (is_array($data) && isset($data['models']) && is_array($data['models'])) {
                foreach ($data['models'] as $m) {
                    if (isset($m['name']) && is_string($m['name'])) {
                        $name = str_replace('models/', '', $m['name']);
                        $displayName = isset($m['displayName']) && is_string($m['displayName']) ? $m['displayName'] : $name;
                        if (str_contains(strtolower($name), 'gemini') && ! str_contains(strtolower($name), 'embedding') && ! str_contains(strtolower($name), 'aqa')) {
                            $models[] = [
                                'id' => $name,
                                'name' => $displayName,
                            ];
                        }
                    }
                }
            }

            return count($models) > 0 ? $models : $this->getDefaultModels();
        } catch (\Exception $e) {
            Log::warning('Gemini GetModels Exception: '.$e->getMessage());

            return $this->getDefaultModels();
        }
    }

    public function testConnection(): bool
    {
        if (in_array($this->apiKey, [null, '', '0'], true)) {
            throw new \Exception('API Key is missing.');
        }

        $response = $this->withApiKeyHeaders()->get("{$this->baseUrl}/models");

        if ($response->failed()) {
            $this->handleError($response);
        }

        return true;
    }

    protected function withApiKeyHeaders(): PendingRequest
    {
        return Http::timeout(30)->withHeaders([
            'x-goog-api-key' => $this->apiKey,
        ]);
    }

    protected function handleError(Response $response): void
    {
        $status = $response->status();
        $error = $response->json('error.message') ?? $response->body();

        Log::error('Gemini API Error', ['status' => $status, 'error' => $error]);

        throw match ($status) {
            400 => new \Exception("Gemini API Bad Request: {$error}"),
            401, 403 => new \Exception("Gemini Authentication Failed: Invalid API Key ({$error})"),
            404 => new \Exception("Gemini Model Not Found: {$error}"),
            429 => new \Exception("Gemini Rate Limit Exceeded or Quota Exhausted: {$error}"),
            default => new \Exception("Gemini API Error ({$status}): {$error}"),
        };
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    protected function getDefaultModels(): array
    {
        return [
            ['id' => 'gemini-2.0-flash', 'name' => 'Gemini 2.0 Flash (Recommended)'],
            ['id' => 'gemini-1.5-pro', 'name' => 'Gemini 1.5 Pro'],
            ['id' => 'gemini-1.5-flash', 'name' => 'Gemini 1.5 Flash'],
        ];
    }
}
