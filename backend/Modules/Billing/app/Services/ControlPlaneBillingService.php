<?php

declare(strict_types=1);

namespace Modules\Billing\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Member\Models\Member;

class ControlPlaneBillingService
{
    public function getBaseUrl(): string
    {
        $url = config('billing.control_plane_url', 'http://127.0.0.1:8080');

        return rtrim(is_string($url) && $url !== '' ? $url : 'http://127.0.0.1:8080', '/');
    }

    public function getTimeout(): int
    {
        $timeout = config('billing.timeout', 5);

        return is_numeric($timeout) ? (int) $timeout : 5;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchPackages(): array
    {
        try {
            $response = Http::timeout($this->getTimeout())
                ->acceptJson()
                ->get($this->getBaseUrl().'/api/v1/public/packages');

            if ($response->successful()) {
                $data = $response->json('data');

                if (is_array($data)) {
                    /** @var array<int, array<string, mixed>> $items */
                    $items = array_values(array_filter($data, 'is_array'));

                    return $items;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to fetch packages from Control Plane', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchMemberBilling(Member $member): array
    {
        try {
            $response = Http::timeout($this->getTimeout())
                ->acceptJson()
                ->get($this->getBaseUrl().'/api/v1/public/subscriptions/lookup', [
                    'email' => $member->email,
                ]);

            if ($response->successful()) {
                $data = $response->json('data');

                return is_array($data) ? $data : [
                    'has_subscription' => false,
                    'subscription' => null,
                    'package' => null,
                    'transactions' => [],
                    'pending_count' => 0,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to fetch member billing from Control Plane', [
                'member_id' => $member->id,
                'email' => $member->email,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'has_subscription' => false,
            'subscription' => null,
            'package' => null,
            'transactions' => [],
            'pending_count' => 0,
            'error' => 'Control Plane connection unavailable',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createOrder(Member $member, array $payload): array
    {
        $orderData = [
            'customer_name' => $member->name,
            'customer_email' => $member->email,
            'package_id' => $payload['package_id'],
            'billing_period' => $payload['billing_period'] ?? 'monthly',
            'payment_method' => $payload['payment_method'] ?? 'midtrans',
            'phone' => $member->phone ?? ($payload['phone'] ?? null),
            'callback_url' => url('/api/v1/public/billing/webhook'),
            'domain' => $payload['domain'] ?? null,
            'notes' => $payload['notes'] ?? null,
        ];

        $response = Http::timeout($this->getTimeout())
            ->acceptJson()
            ->post($this->getBaseUrl().'/api/v1/public/subscriptions/order', $orderData);

        if (! $response->successful()) {
            $msg = $response->json('message') ?? 'Failed to create order on Control Plane';
            throw new \RuntimeException(is_string($msg) ? $msg : 'Order failed');
        }

        $data = $response->json('data');

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkoutTransaction(string $transactionId, string $billingPeriod = 'monthly'): array
    {
        $response = Http::timeout($this->getTimeout())
            ->acceptJson()
            ->post($this->getBaseUrl()."/api/v1/public/subscriptions/transactions/{$transactionId}/checkout", [
                'billing_period' => $billingPeriod,
            ]);

        if (! $response->successful()) {
            $msg = $response->json('message') ?? 'Failed to checkout transaction on Control Plane';
            throw new \RuntimeException(is_string($msg) ? $msg : 'Checkout failed');
        }

        $data = $response->json('data');

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(array $payload): bool
    {
        Log::info('Received billing payment webhook from Control Plane', $payload);

        return true;
    }
}
