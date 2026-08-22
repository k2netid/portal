<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Services\Ai\AiProviderFactory;

class AiController extends BaseApiController
{
    /**
     * Get list of available AI providers.
     */
    public function getProviders(): JsonResponse
    {
        return $this->success(AiProviderFactory::getProviders(), 'AI providers retrieved successfully');
    }

    /**
     * Get available models for a provider.
     */
    public function getModels(Request $request, string $provider): JsonResponse
    {
        try {
            $apiKeyRaw = $request->input('api_key');
            $apiKey = is_string($apiKeyRaw) && $apiKeyRaw !== '' ? $apiKeyRaw : null;

            $service = AiProviderFactory::make($provider, $apiKey);
            $models = $service->getModels();

            return $this->success($models, 'Models retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to fetch models: '.$e->getMessage(), 400);
        }
    }

    /**
     * Test connection to an AI provider.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|string',
            'api_key' => 'required|string',
        ]);

        try {
            $providerRaw = $request->input('provider');
            $provider = is_string($providerRaw) ? $providerRaw : '';
            $apiKeyRaw = $request->input('api_key');
            $apiKey = is_string($apiKeyRaw) ? $apiKeyRaw : '';

            $service = AiProviderFactory::make($provider, $apiKey);
            $service->testConnection();

            return $this->success(null, 'Connection successful!');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Generate content using AI (for Editor Copilot / Assist).
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:4000',
            'context' => 'nullable|string|max:20000',
            'provider' => 'nullable|string',
            'model' => 'nullable|string',
        ]);

        try {
            $providerNameRaw = $request->input('provider');
            $providerName = is_string($providerNameRaw) && $providerNameRaw !== '' ? $providerNameRaw : null;
            $modelRaw = $request->input('model', '');
            $model = is_string($modelRaw) ? $modelRaw : '';
            $promptRaw = $request->input('prompt', '');
            $prompt = is_string($promptRaw) ? $promptRaw : '';
            $contextRaw = $request->input('context', '');
            $context = is_string($contextRaw) ? $contextRaw : '';

            $service = AiProviderFactory::make($providerName);
            $result = $service->generateText($prompt, $context, $model);

            return $this->success([
                'content' => $result,
                'provider' => $service->getName(),
            ], 'Content generated successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 500;

            if (str_contains(strtolower($message), 'quota') || str_contains(strtolower($message), 'rate limit') || str_contains(strtolower($message), 'balance') || str_contains(strtolower($message), 'credit')) {
                $status = 429;
                $message = 'AI Quota, Rate Limit, or Balance Exceeded. Please check your provider account.';
            } elseif (str_contains(strtolower($message), 'not found') || str_contains(strtolower($message), 'supported')) {
                $status = 404;
            } elseif (str_contains(strtolower($message), 'api key') || str_contains(strtolower($message), 'unauthorized') || str_contains(strtolower($message), 'authentication')) {
                $status = 401;
            }

            return $this->error($message, $status, [], 'AI_ERROR', ['original_error' => $e->getMessage()]);
        }
    }
}
