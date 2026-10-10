<?php

declare(strict_types=1);

namespace Modules\Billing\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Modules\Member\Tests\Concerns\SoftensPasswordPolicyForTests;
use Tests\TestCase;

class MemberBillingIntegrationTest extends TestCase
{
    use SoftensPasswordPolicyForTests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->softenPasswordPolicyForTests();
        $this->activatePack('member');
    }

    /**
     * @return array{token: string, id: string}
     */
    private function registerMember(): array
    {
        $response = $this->postJson('/api/v1/public/member/register', [
            'name' => 'K2NET Customer',
            'email' => 'customer@k2net.id',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ]);

        $response->assertCreated();

        $token = (string) $response->json('data.token');
        $id = (string) $response->json('data.member.id');

        return [
            'token' => $token,
            'id' => $id,
        ];
    }

    public function test_packages_endpoint_forwards_to_control_plane(): void
    {
        Http::fake([
            '*/api/v1/public/packages' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 'k2-fiber-100',
                        'name' => 'K2NET Dedicated 100 Mbps',
                        'price_monthly' => 1500000,
                        'price_yearly' => 16500000,
                        'feature_highlights' => ['1:1 Ratio', 'SLA 99.5%'],
                    ],
                ],
            ], 200),
        ]);

        $auth = $this->registerMember();

        $response = $this->withHeader('Authorization', 'Bearer '.$auth['token'])
            ->getJson('/api/v1/member/billing/packages');

        $response->assertOk();
        $response->assertJsonPath('data.0.id', 'k2-fiber-100');
        $response->assertJsonPath('data.0.name', 'K2NET Dedicated 100 Mbps');
    }

    public function test_member_billing_summary_lookup(): void
    {
        Http::fake([
            '*/api/v1/public/subscriptions/lookup*' => Http::response([
                'success' => true,
                'data' => [
                    'has_subscription' => true,
                    'subscription' => [
                        'id' => 'sub-123',
                        'name' => 'Active Fiber',
                        'status' => 'active',
                    ],
                    'package' => [
                        'name' => 'K2NET Dedicated 100 Mbps',
                        'price_monthly' => 1500000,
                    ],
                    'transactions' => [
                        [
                            'id' => 'txn-1',
                            'invoice_number' => 'INV-20261010-001',
                            'payment_status' => 'pending',
                            'amount' => 1500000,
                            'can_checkout' => true,
                        ],
                    ],
                    'pending_count' => 1,
                ],
            ], 200),
        ]);

        $auth = $this->registerMember();

        $response = $this->withHeader('Authorization', 'Bearer '.$auth['token'])
            ->getJson('/api/v1/member/billing');

        $response->assertOk();
        $response->assertJsonPath('data.has_subscription', true);
        $response->assertJsonPath('data.subscription.id', 'sub-123');
        $response->assertJsonPath('data.pending_count', 1);
    }

    public function test_member_order_creates_control_plane_order(): void
    {
        Http::fake([
            '*/api/v1/public/subscriptions/order' => Http::response([
                'success' => true,
                'data' => [
                    'subscription' => [
                        'id' => 'sub-new',
                        'name' => 'K2NET Fiber - K2NET Customer',
                        'status' => 'pending',
                    ],
                    'transaction' => [
                        'id' => 'txn-new',
                        'invoice_number' => 'INV-20261010-999',
                        'amount' => 1500000,
                        'payment_status' => 'pending',
                    ],
                    'checkout' => [
                        'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-token',
                        'client_token' => 'mock-token',
                    ],
                ],
            ], 201),
        ]);

        $auth = $this->registerMember();

        $response = $this->withHeader('Authorization', 'Bearer '.$auth['token'])
            ->postJson('/api/v1/member/billing/order', [
                'package_id' => 'k2-fiber-100',
                'billing_period' => 'monthly',
                'payment_method' => 'midtrans',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.subscription.id', 'sub-new');
        $response->assertJsonPath('data.checkout.client_token', 'mock-token');
    }

    public function test_member_checkout_transaction(): void
    {
        Http::fake([
            '*/api/v1/public/subscriptions/transactions/txn-abc/checkout' => Http::response([
                'success' => true,
                'data' => [
                    'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/token-abc',
                    'client_token' => 'token-abc',
                ],
            ], 200),
        ]);

        $auth = $this->registerMember();

        $response = $this->withHeader('Authorization', 'Bearer '.$auth['token'])
            ->postJson('/api/v1/member/billing/transactions/txn-abc/checkout', [
                'billing_period' => 'monthly',
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.client_token', 'token-abc');
    }

    public function test_billing_webhook_endpoint(): void
    {
        $response = $this->postJson('/api/v1/public/billing/webhook', [
            'event' => 'subscription.paid',
            'subscription_id' => 'sub-123',
            'transaction_id' => 'txn-1',
            'status' => 'active',
            'customer_email' => 'customer@k2net.id',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.received', true);
    }
}
