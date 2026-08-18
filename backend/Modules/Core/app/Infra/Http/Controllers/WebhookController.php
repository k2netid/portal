<?php

namespace Modules\Core\Infra\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\Security\Rules\SafeUrl;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Webhook;

class WebhookController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Webhook::query();

        if ($request->has('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $webhooks = $query->latest()->get();

        return $this->success($webhooks, 'Webhooks retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'url' => ['required', 'url', new SafeUrl],
                'event' => 'required|string',
                'method' => 'nullable|in:POST,PUT,PATCH',
                'headers' => 'nullable|array',
                'payload_template' => 'nullable|array',
                'timeout' => 'integer|min:1|max:300',
                'max_retries' => 'integer|min:0|max:10',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $webhook = Webhook::create($validated);

        return $this->success($webhook, 'Webhook created successfully', 201);
    }

    public function show(Webhook $webhook): JsonResponse
    {
        return $this->success($webhook, 'Webhook retrieved successfully');
    }

    public function update(Request $request, Webhook $webhook): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'url' => ['sometimes', 'required', 'url', new SafeUrl],
                'event' => 'sometimes|required|string',
                'method' => 'nullable|in:POST,PUT,PATCH',
                'headers' => 'nullable|array',
                'payload_template' => 'nullable|array',
                'is_active' => 'boolean',
                'timeout' => 'integer|min:1|max:300',
                'max_retries' => 'integer|min:0|max:10',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $webhook->update($validated);

        return $this->success($webhook, 'Webhook updated successfully');
    }

    public function destroy(Webhook $webhook): JsonResponse
    {
        $webhook->delete();

        return $this->success(null, 'Webhook deleted successfully');
    }

    public function test(Webhook $webhook): JsonResponse
    {
        $testData = [
            'test' => true,
            'timestamp' => now()->toIso8601String(),
        ];

        $result = $webhook->trigger($testData);

        return $this->success([
            'success' => $result,
        ], $result ? 'Webhook triggered successfully' : 'Webhook failed');
    }

    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => Webhook::count(),
            'active' => Webhook::where('is_active', true)->count(),
            'total_success' => (int) Webhook::sum('success_count'),
            'total_failures' => (int) Webhook::sum('failure_count'),
            'recent_webhooks' => Webhook::whereNotNull('last_triggered_at')
                ->latest('last_triggered_at')
                ->limit(10)
                ->get(),
        ];

        return $this->success($stats, 'Webhook statistics retrieved successfully');
    }
}
