<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Jobs\ProcessOutboundWebhook;
use Modules\Core\System\Models\Webhook;
use Modules\Core\System\Models\WebhookDelivery;

class WebhookController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $webhooks = Webhook::orderBy('created_at', 'desc')->get();

        return $this->success($webhooks, 'Webhooks retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'events' => 'required|array',
            'events.*' => 'string',
            'secret' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $webhook = Webhook::create($validated);

        return $this->success($webhook, 'Webhook created successfully', 201);
    }

    public function show(Webhook $webhook): JsonResponse
    {
        return $this->success($webhook, 'Webhook details retrieved');
    }

    public function update(Request $request, Webhook $webhook): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|url|max:255',
            'events' => 'sometimes|required|array',
            'events.*' => 'string',
            'secret' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $webhook->update($validated);

        return $this->success($webhook, 'Webhook updated successfully');
    }

    public function destroy(Webhook $webhook): JsonResponse
    {
        $webhook->delete();

        return $this->success(null, 'Webhook deleted successfully');
    }

    public function recentDeliveries(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->query('limit', 50), 1), 100);

        $deliveries = WebhookDelivery::query()
            ->with('webhook:id,name')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $this->success($deliveries, 'Recent webhook deliveries');
    }

    public function deliveries(Request $request, Webhook $webhook): JsonResponse
    {
        $limit = min(max((int) $request->query('limit', 50), 1), 100);

        $deliveries = $webhook->deliveries()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $this->success($deliveries, 'Webhook delivery history');
    }

    public function trigger(Request $request, Webhook $webhook): JsonResponse
    {
        /** @var array{payload: array<string, mixed>} $validated */
        $validated = $request->validate([
            'payload' => 'required|array',
        ]);

        ProcessOutboundWebhook::dispatch(
            $webhook,
            'test.ping',
            $validated['payload']
        );

        return $this->success(null, 'Test payload dispatched');
    }
}
