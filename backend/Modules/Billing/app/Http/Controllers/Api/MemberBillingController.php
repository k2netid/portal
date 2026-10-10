<?php

declare(strict_types=1);

namespace Modules\Billing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Billing\Services\ControlPlaneBillingService;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Member\Models\Member;

class MemberBillingController extends BaseApiController
{
    public function __construct(
        private readonly ControlPlaneBillingService $billingService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        $summary = $this->billingService->fetchMemberBilling($member);

        return $this->success($summary, 'Member billing summary');
    }

    public function packages(): JsonResponse
    {
        $packages = $this->billingService->fetchPackages();

        return $this->success($packages, 'Available packages');
    }

    public function order(Request $request): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        $validated = $request->validate([
            'package_id' => 'required|string',
            'billing_period' => 'nullable|in:monthly,yearly',
            'payment_method' => 'nullable|string|in:midtrans,xendit,manual',
            'phone' => 'nullable|string|max:32',
            'domain' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->billingService->createOrder($member, $validated);

            return $this->success($result, 'Subscription order created', 201);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function checkoutTransaction(Request $request, string $id): JsonResponse
    {
        $member = $request->user('member');
        if (! $member instanceof Member) {
            return $this->error('Unauthenticated', 401);
        }

        $validated = $request->validate([
            'billing_period' => 'nullable|in:monthly,yearly',
        ]);

        try {
            $result = $this->billingService->checkoutTransaction($id, $validated['billing_period'] ?? 'monthly');

            return $this->success($result, 'Checkout session created');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $this->billingService->handleWebhook($payload);

        return $this->success(['received' => true], 'Webhook processed');
    }
}
