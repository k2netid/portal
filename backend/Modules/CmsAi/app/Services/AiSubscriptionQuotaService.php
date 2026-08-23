<?php

declare(strict_types=1);

namespace Modules\CmsAi\Services;

use Illuminate\Support\Facades\Schema;
use Modules\CmsAi\Models\AiUsageLog;
use Modules\CmsAi\Services\Exceptions\AiSubscriptionQuotaExceededException;
use Modules\Core\System\Support\HubSubscriptionScope;

class AiSubscriptionQuotaService
{
    public function monthlyTokenLimit(?string $subscriptionId = null): ?int
    {
        return null;
    }

    public function monthlyTokensUsed(?string $subscriptionId = null): int
    {
        if (! Schema::hasTable('ai_usage_logs')) {
            return 0;
        }

        $since = now()->startOfMonth();
        $resolvedSubId = $this->resolveSubscriptionId($subscriptionId);

        $row = AiUsageLog::query()
            ->where('created_at', '>=', $since)
            ->where('subscription_id', $resolvedSubId)
            ->selectRaw('COALESCE(SUM(tokens_in), 0) + COALESCE(SUM(tokens_out), 0) as total')
            ->first();

        return (int) ($row->total ?? 0);
    }

    public function assertCanConsume(?string $subscriptionId, int $estimatedTokens): void
    {
        $limit = $this->monthlyTokenLimit($subscriptionId);
        if ($limit === null) {
            return;
        }

        if ($limit === 0) {
            throw new AiSubscriptionQuotaExceededException(0, 0, max(0, $estimatedTokens));
        }

        $used = $this->monthlyTokensUsed($subscriptionId);
        if (($used + max(0, $estimatedTokens)) > $limit) {
            throw new AiSubscriptionQuotaExceededException($limit, $used, max(0, $estimatedTokens));
        }
    }

    private function resolveSubscriptionId(?string $subscriptionId): ?string
    {
        if ($subscriptionId !== null && trim($subscriptionId) !== '') {
            return $subscriptionId;
        }

        $fromScope = HubSubscriptionScope::id();

        return is_scalar($fromScope) && trim((string) $fromScope) !== ''
            ? (string) $fromScope
            : null;
    }
}
