<?php

declare(strict_types=1);

namespace Modules\Intelligence\Ai\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Intelligence\Ai\Models\AiUsageLog;
use Modules\Intelligence\Ai\Services\AiProviderFactory;
use Modules\Intelligence\Ai\Services\AiSubscriptionQuotaService;
use Modules\Intelligence\Ai\Services\AiUsageRecorder;
use Modules\Intelligence\Ai\Services\Exceptions\AiSubscriptionQuotaExceededException;
use Modules\Intelligence\Ai\Services\Exceptions\PublishingDraftParseException;
use Modules\Intelligence\Ai\Services\Exceptions\TaxonomySuggestParseException;
use Modules\Intelligence\Ai\Services\Providers\DeepSeekService;
use Modules\Intelligence\Ai\Services\Providers\GeminiService;
use Modules\Intelligence\Ai\Services\Providers\OpenAiService;
use Modules\Intelligence\Ai\Services\PublishingContentDraftService;
use Modules\Intelligence\Ai\Services\PublishingTaxonomySuggestService;

class AiController extends BaseApiController
{
    public function __construct(
        private readonly PublishingContentDraftService $publishingDrafts,
        private readonly PublishingTaxonomySuggestService $taxonomySuggest,
        private readonly AiUsageRecorder $usageRecorder,
        private readonly AiSubscriptionQuotaService $subscriptionQuota,
    ) {}

    /**
     * Get list of available AI providers
     */
    public function getProviders(): JsonResponse
    {
        return $this->success(AiProviderFactory::getProviders());
    }

    public function usageStats(): JsonResponse
    {
        $since = now()->subDays(30);

        $rows = AiUsageLog::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('feature, provider, COUNT(*) as calls, SUM(tokens_in) as tokens_in, SUM(tokens_out) as tokens_out')
            ->groupBy('feature', 'provider')
            ->orderByDesc('calls')
            ->get();

        $totalCalls = AiUsageLog::query()->where('created_at', '>=', $since)->count();

        return $this->success([
            'period_days' => 30,
            'total_calls' => $totalCalls,
            'monthly_token_limit' => $this->subscriptionQuota->monthlyTokenLimit(null),
            'monthly_tokens_used' => $this->subscriptionQuota->monthlyTokensUsed(),
            'by_feature' => $rows,
        ], 'AI usage statistics');
    }

    /**
     * Get available models for a provider
     */
    public function getModels(Request $request, string $provider): JsonResponse
    {
        try {
            // Instantiate service manually with the provided key (for setup) or null to use saved key
            $apiKeyRaw = $request->input('api_key');
            $apiKey = is_string($apiKeyRaw) ? $apiKeyRaw : null;

            // Factory doesn't accept key in 'make', so we instantiate manually based on provider
            $service = match ($provider) {
                'openai' => new OpenAiService($apiKey),
                'deepseek' => new DeepSeekService($apiKey),
                'gemini' => new GeminiService($apiKey),
                default => throw new \Exception('Unknown provider'),
            };

            $models = $service->getModels();

            return $this->success($models);
        } catch (\Exception $e) {
            return $this->error('Failed to fetch models: '.$e->getMessage(), 500);
        }
    }

    /**
     * Test connection to a provider
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
            $apiKey = is_string($apiKeyRaw) ? $apiKeyRaw : null;

            $service = match ($provider) {
                'openai' => new OpenAiService($apiKey),
                'deepseek' => new DeepSeekService($apiKey),
                'gemini' => new GeminiService($apiKey),
                default => throw new \Exception('Unknown provider'),
            };

            $service->testConnection();

            return $this->success(null, 'Connection successful!');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Generate content using AI
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'context' => 'nullable|string|max:5000',
            'provider' => 'nullable|string',
            'model' => 'nullable|string',
        ]);

        try {
            $providerNameRaw = $request->input('provider');
            $providerName = is_string($providerNameRaw) ? $providerNameRaw : null;
            $modelRaw = $request->input('model', '');
            $model = is_string($modelRaw) ? $modelRaw : '';
            $promptRaw = $request->input('prompt', '');
            $prompt = is_string($promptRaw) ? $promptRaw : '';
            $contextRaw = $request->input('context', '');
            $context = is_string($contextRaw) ? $contextRaw : '';

            $estimateIn = (int) (strlen($prompt.$context) / 4);
            $this->subscriptionQuota->assertCanConsume(null, $estimateIn + 2000);

            // Use Factory to get the active service
            $service = AiProviderFactory::make($providerName);

            $result = $service->generateText(
                $prompt,
                $context,
                $model
            );

            $this->usageRecorder->record(
                'generate',
                $service->getName(),
                tokensIn: (int) (strlen($prompt) / 4),
                tokensOut: (int) (strlen($result) / 4),
            );

            return $this->success([
                'content' => $result,
                'provider' => $service->getName(),
            ]);

        } catch (AiSubscriptionQuotaExceededException $e) {
            return $this->error($e->getMessage(), 429, [], 'AI_SUBSCRIPTION_QUOTA', [
                'limit' => $e->limit,
                'used' => $e->used,
                'requested' => $e->requested,
            ]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 500;

            if (str_contains(strtolower($message), 'quota') || str_contains(strtolower($message), 'insufficient balance')) {
                $status = 429;
                $message = 'AI Quota/Balance Exceeded. Please check your billing.';
            } elseif (str_contains(strtolower($message), 'not found') || str_contains(strtolower($message), 'supported')) {
                $status = 404;
            } elseif (str_contains(strtolower($message), 'api key') || str_contains(strtolower($message), 'unauthorized')) {
                $status = 401;
            }

            return $this->error($message, $status, [], 'AI_ERROR', ['original_error' => $e->getMessage()]);
        }
    }

    /**
     * Generate a structured publishing draft (title, excerpt, intro, body).
     */
    public function draftPublishing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:500',
            'content_type' => 'nullable|in:post,page',
            'category_name' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:64',
            'tone' => 'nullable|string|max:64',
            'provider' => 'nullable|string',
            'model' => 'nullable|string',
        ]);

        try {
            $this->subscriptionQuota->assertCanConsume(null, 800);

            $draft = $this->publishingDrafts->draft($validated);

            $this->usageRecorder->record(
                'draft_publishing',
                is_string($validated['provider'] ?? null) ? $validated['provider'] : null,
                tokensOut: 500,
            );

            return $this->success($draft, 'Publishing draft generated');
        } catch (AiSubscriptionQuotaExceededException $e) {
            return $this->error($e->getMessage(), 429, [], 'AI_SUBSCRIPTION_QUOTA', [
                'limit' => $e->limit,
                'used' => $e->used,
                'requested' => $e->requested,
            ]);
        } catch (PublishingDraftParseException $e) {
            return $this->error($e->getMessage(), 422, [], 'AI_DRAFT_PARSE_ERROR');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500, [], 'AI_ERROR');
        }
    }

    /**
     * Suggest category and tags for publishing content (batch taxonomy assist).
     */
    public function suggestTaxonomy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'nullable|string|max:50000',
            'existing_categories' => 'nullable|array',
            'existing_categories.*' => 'string|max:255',
            'existing_tags' => 'nullable|array',
            'existing_tags.*' => 'string|max:64',
            'provider' => 'nullable|string',
            'model' => 'nullable|string',
        ]);

        try {
            $this->subscriptionQuota->assertCanConsume(null, 300);

            $suggestion = $this->taxonomySuggest->suggest($validated);

            $this->usageRecorder->record(
                'suggest_taxonomy',
                is_string($validated['provider'] ?? null) ? $validated['provider'] : null,
                tokensOut: 200,
            );

            return $this->success($suggestion, 'Taxonomy suggestions generated');
        } catch (AiSubscriptionQuotaExceededException $e) {
            return $this->error($e->getMessage(), 429, [], 'AI_SUBSCRIPTION_QUOTA', [
                'limit' => $e->limit,
                'used' => $e->used,
                'requested' => $e->requested,
            ]);
        } catch (TaxonomySuggestParseException $e) {
            return $this->error($e->getMessage(), 422, [], 'AI_TAXONOMY_PARSE_ERROR');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500, [], 'AI_ERROR');
        }
    }
}
