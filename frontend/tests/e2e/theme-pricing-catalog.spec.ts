import { expect, test } from '@playwright/test';

const fulfillJson = (body: unknown) => ({
  status: 200,
  contentType: 'application/json',
  body: JSON.stringify(body),
});

const themeJson = {
  success: true,
  data: {
    slug: 'janari',
    path: 'janari',
    source: 'bundled',
    settings: { animation_enabled: false },
    manifest: {},
    assets: { css: [] as string[], js: [] as string[] },
  },
};

test.describe('Pricing catalog', () => {
  test.beforeEach(async ({ page }) => {
    await page.route('**/api/v1/public/layout/themes/active**', (r) =>
      r.fulfill(fulfillJson(themeJson)),
    );
    await page.route('**/api/v1/public/layout/plugin-blocks**', (r) =>
      r.fulfill(fulfillJson({ success: true, data: { plugins: [], slots: [] } })),
    );
    await page.route('**/api/v1/public/system/settings**', (r) =>
      r.fulfill(fulfillJson({ success: true, data: { site_name: 'E2E' } })),
    );
    await page.route('**/api/v1/public/layout/menus/**', (r) =>
      r.fulfill(fulfillJson({ success: true, data: [] })),
    );
    await page.route('**/api/v1/public/platform/catalog**', async (route) => {
      await route.fulfill(
        fulfillJson({
          success: true,
          data: {
            live: true,
            products: [
              {
                id: 'hub',
                name: 'Jejakawan Hub',
                description: 'Test',
                packages: [
                  {
                    id: 'hub-starter',
                    name: 'Hub Starter',
                    price_monthly: 299000,
                    price_yearly: 2990000,
                    user_limit: 25,
                    storage_limit_mb: 2048,
                    ai_monthly_token_limit: 50000,
                    feature_highlights: ['Publishing & pages'],
                  },
                ],
              },
            ],
          },
        }),
      );
    });
  });

  test('shows live hub packages when catalog API succeeds', async ({ page }) => {
    await page.goto('/pricing');
    await expect(page.getByRole('heading', { name: 'Hub Starter' }).first()).toBeVisible({ timeout: 15_000 });
    await expect(page.getByText(/Rp.*299/).first()).toBeVisible();
  });
});
