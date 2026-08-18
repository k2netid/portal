import { expect, test } from '@playwright/test';

const fulfillJson = (body: unknown) => ({
  status: 200,
  contentType: 'application/json',
  body: JSON.stringify(body),
});

test.describe('Member billing', () => {
  test.beforeEach(async ({ page }) => {
    await page.addInitScript(() => {
      localStorage.setItem('member_auth_token', 'e2e-test-token');
      sessionStorage.setItem('member_subscription_domain', 'demo.jejakawan.com');
    });
    await page.route('**/api/v1/public/member/billing**', async (route) => {
      if (route.request().method() === 'GET') {
        return route.fulfill(
          fulfillJson({
            success: true,
            data: {
              subscription: { status: 'active', user_limit: 25, storage_limit_mb: 2048 },
              package: { name: 'Growth', feature_highlights: ['Member portal', 'Publishing & pages'] },
              pending_count: 1,
              transactions: [
                {
                  id: 'txn-1',
                  invoice_number: 'INV-E2E-1',
                  payment_status: 'pending',
                  amount: 749000,
                  amount_formatted: 'Rp 749.000',
                  can_checkout: true,
                },
              ],
            },
          }),
        );
      }
      return route.continue();
    });
    await page.route('**/api/v1/public/member/profile**', (r) =>
      r.fulfill(fulfillJson({ success: true, data: { user: { name: 'E2E', email: 'e2e@test.com' }, member: {} } })),
    );
  });

  test('shows plan and pending invoice with pay action', async ({ page }) => {
    await page.goto('/member/billing');
    await expect(page.getByText('Growth')).toBeVisible({ timeout: 10_000 });
    await expect(page.getByText('INV-E2E-1')).toBeVisible();
    await expect(page.getByRole('button', { name: /Pay now|Bayar sekarang/i })).toBeVisible();
  });

  test('pay now shows stub checkout message', async ({ page }) => {
    await page.route('**/api/v1/public/member/billing/transactions/txn-1/checkout**', async (route) => {
      if (route.request().method() === 'POST') {
        return route.fulfill(
          fulfillJson({
            success: true,
            data: {
              mode: 'stub',
              provider: 'midtrans',
              message: 'Checkout stub mode is enabled (PLATFORM_PAYMENT_CHECKOUT_STUB).',
              invoice_number: 'INV-E2E-1',
            },
          }),
        );
      }
      return route.continue();
    });

    await page.goto('/member/billing');
    await page.getByRole('button', { name: /Pay now|Bayar sekarang/i }).click();
    await expect(page.getByText(/Checkout stub mode|PLATFORM_PAYMENT_CHECKOUT_STUB/i)).toBeVisible({
      timeout: 10_000,
    });
  });
});

