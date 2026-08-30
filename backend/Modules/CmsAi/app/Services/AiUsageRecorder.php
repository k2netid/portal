<?php

declare(strict_types=1);

namespace Modules\CmsAi\Services;

use Illuminate\Support\Facades\Auth;
use Modules\CmsAi\Models\AiUsageLog;
use Modules\Core\System\Support\HubSubscriptionScope;

class AiUsageRecorder
{
    public function record(
        string $feature,
        ?string $provider = null,
        int $tokensIn = 0,
        int $tokensOut = 0,
        ?int $durationMs = null,
    ): void {
        $user = Auth::user();

        AiUsageLog::query()->create([
            'user_id' => $user?->getAuthIdentifier(),
            'subscription_id' => HubSubscriptionScope::id(),
            'feature' => $feature,
            'provider' => $provider,
            'tokens_in' => max(0, $tokensIn),
            'tokens_out' => max(0, $tokensOut),
            'duration_ms' => $durationMs,
        ]);
    }
}
