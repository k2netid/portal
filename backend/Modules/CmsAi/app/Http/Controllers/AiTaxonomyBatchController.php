<?php

declare(strict_types=1);

namespace Modules\CmsAi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\CmsAi\Models\AiTaxonomyBatch;
use Modules\CmsAi\Services\AiTaxonomyBatchService;
use Modules\CmsAi\Services\Exceptions\AiSubscriptionQuotaExceededException;

class AiTaxonomyBatchController extends BaseApiController
{
    public function __construct(
        private readonly AiTaxonomyBatchService $batches,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $limitRaw = $request->input('limit', 10);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 10;

        $rows = array_map(
            fn (AiTaxonomyBatch $batch): array => $this->serializeBatch($batch),
            $this->batches->recent($limit),
        );

        return $this->success($rows, 'Taxonomy batch jobs retrieved');
    }

    public function show(string $id): JsonResponse
    {
        $batch = $this->batches->find($id);
        if (! $batch) {
            return $this->error('Batch not found', 404);
        }

        return $this->success($this->serializeBatch($batch), 'Taxonomy batch retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1|max:25',
            'items.*.ref' => 'nullable|string|max:64',
            'items.*.title' => 'required|string|max:500',
            'items.*.excerpt' => 'nullable|string|max:1000',
            'items.*.body' => 'nullable|string|max:50000',
            'items.*.existing_categories' => 'nullable|array',
            'items.*.existing_categories.*' => 'string|max:255',
            'items.*.existing_tags' => 'nullable|array',
            'items.*.existing_tags.*' => 'string|max:64',
            'items.*.model' => 'nullable|string|max:128',
            'provider' => 'nullable|string|max:64',
        ]);

        try {
            $batch = $this->batches->createAndDispatch(
                $validated['items'],
                is_string($validated['provider'] ?? null) ? $validated['provider'] : null,
            );
        } catch (AiSubscriptionQuotaExceededException $e) {
            return $this->error($e->getMessage(), 429, [], 'AI_WORKSPACE_QUOTA', [
                'limit' => $e->limit,
                'used' => $e->used,
                'requested' => $e->requested,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            $this->serializeBatch($batch->fresh() ?? $batch),
            'Taxonomy batch queued',
            202,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeBatch(AiTaxonomyBatch $batch): array
    {
        return [
            'id' => $batch->id,
            'status' => $batch->status,
            'total_items' => $batch->total_items,
            'completed_items' => $batch->completed_items,
            'failed_items' => $batch->failed_items,
            'provider' => $batch->provider,
            'error_message' => $batch->error_message,
            'items' => $batch->items,
            'results' => $batch->results,
            'created_at' => $batch->created_at?->toIso8601String(),
            'updated_at' => $batch->updated_at?->toIso8601String(),
        ];
    }
}
