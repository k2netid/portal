<?php

declare(strict_types=1);

namespace Modules\CmsAi\Services;

use Illuminate\Support\Facades\Auth;
use Modules\CmsAi\Jobs\ProcessAiTaxonomyBatchJob;
use Modules\CmsAi\Models\AiTaxonomyBatch;
use Modules\CmsAi\Services\Exceptions\TaxonomySuggestParseException;
use Modules\Core\System\Support\HubSubscriptionScope;

class AiTaxonomyBatchService
{
    private const MAX_ITEMS = 25;

    private const TOKENS_PER_ITEM_ESTIMATE = 300;

    public function __construct(
        private readonly PublishingTaxonomySuggestService $taxonomySuggest,
        private readonly AiUsageRecorder $usageRecorder,
        private readonly AiSubscriptionQuotaService $subscriptionQuota,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createAndDispatch(array $items, ?string $provider = null, ?string $model = null): AiTaxonomyBatch
    {
        $normalized = $this->normalizeItems($items);
        $count = count($normalized);
        $subscriptionId = HubSubscriptionScope::id();

        $this->subscriptionQuota->assertCanConsume(
            $subscriptionId,
            self::TOKENS_PER_ITEM_ESTIMATE * $count,
        );

        $user = Auth::user();
        $userId = $user?->getAuthIdentifier();
        $batch = AiTaxonomyBatch::query()->create([
            'user_id' => is_scalar($userId) ? (string) $userId : null,
            'subscription_id' => $subscriptionId,
            'status' => 'pending',
            'total_items' => $count,
            'items' => $normalized,
            'provider' => $provider,
        ]);

        ProcessAiTaxonomyBatchJob::dispatch($batch->id);

        return $batch->fresh() ?? $batch;
    }

    public function process(string $batchId): void
    {
        /** @var AiTaxonomyBatch|null $batch */
        $batch = AiTaxonomyBatch::query()->find($batchId);
        if (! $batch || $batch->status !== 'pending') {
            return;
        }

        $batch->update(['status' => 'processing']);

        $items = is_array($batch->items) ? $batch->items : [];
        $results = [];
        $completed = 0;
        $failed = 0;
        $providerName = $batch->provider;

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                $results[] = $this->failedRow((string) $index, 'Invalid item payload');
                $failed++;

                continue;
            }

            $ref = is_scalar($item['ref'] ?? null) ? (string) $item['ref'] : (string) $index;

            try {
                $suggestion = $this->taxonomySuggest->suggest([
                    'title' => $this->requiredString($item['title'] ?? '', 'title'),
                    'excerpt' => $this->optionalString($item['excerpt'] ?? null),
                    'body' => $this->optionalString($item['body'] ?? null),
                    'existing_categories' => $this->stringList($item['existing_categories'] ?? []),
                    'existing_tags' => $this->stringList($item['existing_tags'] ?? []),
                    'provider' => $batch->provider,
                    'model' => $this->optionalString($item['model'] ?? null),
                ]);

                $providerName = $suggestion['provider'];

                $this->usageRecorder->record(
                    'suggest_taxonomy_batch',
                    $suggestion['provider'],
                    tokensOut: 200,
                );

                $results[] = [
                    'ref' => $ref,
                    'status' => 'completed',
                    'result' => $suggestion,
                ];
                $completed++;
            } catch (TaxonomySuggestParseException $e) {
                $results[] = $this->failedRow($ref, $e->getMessage());
                $failed++;
            } catch (\Throwable $e) {
                $results[] = $this->failedRow($ref, $e->getMessage());
                $failed++;
            }
        }

        $batch->update([
            'status' => $failed === $batch->total_items ? 'failed' : 'completed',
            'completed_items' => $completed,
            'failed_items' => $failed,
            'results' => $results,
            'provider' => $providerName,
            'error_message' => $failed > 0 ? "{$failed} item(s) failed" : null,
        ]);
    }

    /**
     * @return array<int, AiTaxonomyBatch>
     */
    public function recent(int $limit = 10): array
    {
        $subscriptionId = HubSubscriptionScope::id();

        return AiTaxonomyBatch::query()
            ->where('subscription_id', $subscriptionId)
            ->orderByDesc('created_at')
            ->limit(max(1, min(50, $limit)))
            ->get()
            ->all();
    }

    public function find(string $id): ?AiTaxonomyBatch
    {
        $subscriptionId = HubSubscriptionScope::id();

        return AiTaxonomyBatch::query()
            ->where('subscription_id', $subscriptionId)
            ->find($id);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        if ($items === []) {
            throw new \InvalidArgumentException('At least one item is required');
        }

        if (count($items) > self::MAX_ITEMS) {
            throw new \InvalidArgumentException('Maximum '.self::MAX_ITEMS.' items per batch');
        }

        $normalized = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                throw new \InvalidArgumentException('Each batch item must be an object');
            }

            $title = trim($this->requiredString($item['title'] ?? '', 'title'));
            if ($title === '') {
                throw new \InvalidArgumentException("Item at index {$index} requires a title");
            }

            $normalized[] = [
                'ref' => is_scalar($item['ref'] ?? null) ? (string) $item['ref'] : (string) $index,
                'title' => $title,
                'excerpt' => $this->optionalString($item['excerpt'] ?? null),
                'body' => $this->optionalString($item['body'] ?? null),
                'existing_categories' => $this->stringList($item['existing_categories'] ?? []),
                'existing_tags' => $this->stringList($item['existing_tags'] ?? []),
                'model' => $this->optionalString($item['model'] ?? null),
            ];
        }

        return $normalized;
    }

    private function requiredString(mixed $value, string $field): string
    {
        if (! is_scalar($value)) {
            throw new \InvalidArgumentException("{$field} must be a string");
        }

        return (string) $value;
    }

    private function optionalString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_scalar($value)) {
            return null;
        }

        return (string) $value;
    }

    /**
     * @return array<int, string>
     */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $entry) {
            if (is_scalar($entry)) {
                $s = trim((string) $entry);
                if ($s !== '') {
                    $out[] = $s;
                }
            }
        }

        return $out;
    }

    /**
     * @return array{ref: string, status: string, error: string}
     */
    private function failedRow(string $ref, string $message): array
    {
        return [
            'ref' => $ref,
            'status' => 'failed',
            'error' => mb_substr($message, 0, 500),
        ];
    }
}
