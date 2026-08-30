<?php

declare(strict_types=1);

namespace Modules\CmsAi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CmsAi\Models\AiUsageLog;
use Modules\CmsAi\Services\AiSubscriptionQuotaService;
use Modules\CmsAi\Services\AiUsageRecorder;
use Modules\CmsAi\Services\Exceptions\AiSubscriptionQuotaExceededException;
use Modules\CmsAi\Services\Exceptions\PublishingDraftParseException;
use Modules\CmsAi\Services\Exceptions\TaxonomySuggestParseException;
use Modules\CmsAi\Services\PublishingContentDraftService;
use Modules\CmsAi\Services\PublishingTaxonomySuggestService;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Services\Ai\AiAvailability;

class AiController extends BaseApiController
{
    public function __construct(
        private readonly PublishingContentDraftService $publishingDrafts,
        private readonly PublishingTaxonomySuggestService $taxonomySuggest,
        private readonly AiUsageRecorder $usageRecorder,
        private readonly AiSubscriptionQuotaService $subscriptionQuota,
    ) {}

    public function usageStats(): JsonResponse
    {
        if ($denied = $this->denyUnlessAiEnabled()) {
            return $denied;
        }

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

    public function draftPublishing(Request $request): JsonResponse
    {
        if ($denied = $this->denyUnlessAiEnabled()) {
            return $denied;
        }

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

    public function suggestTaxonomy(Request $request): JsonResponse
    {
        if ($denied = $this->denyUnlessAiEnabled()) {
            return $denied;
        }

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

    protected function denyUnlessAiEnabled(): ?JsonResponse
    {
        if (AiAvailability::isGloballyEnabled()) {
            return null;
        }

        return $this->error(
            'Global AI is disabled in Settings → AI.',
            403,
            [],
            'AI_DISABLED',
        );
    }
}
