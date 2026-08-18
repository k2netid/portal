import { expect, test } from '@playwright/test';

const fulfillJson = (body: unknown) => ({
  status: 200,
  contentType: 'application/json',
  body: JSON.stringify(body),
});

const themeJson = {
  success: true,
  data: {
    id: '1',
    name: 'Janari',
    slug: 'janari',
    type: 'frontend',
    path: 'janari',
    source: 'bundled',
    version: '1.0.0',
    settings: { animation_enabled: false },
    custom_css: '',
    assets: { css: [] as string[], js: [] as string[] },
    manifest: { name: 'Janari' },
  },
};

test.describe('Janari public smoke', () => {
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
    await page.route('**/api/v1/public/publishing/contents**', (r) => {
      if (r.request().method() !== 'GET') return r.continue();
      return r.fulfill(fulfillJson({ success: true, data: { data: [], meta: { total: 0 } } }));
    });
  });

  test('home loads', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('body')).toBeVisible();
  });

  test('blog index loads', async ({ page }) => {
    await page.goto('/blog');
    await expect(page.locator('body')).toBeVisible();
  });
});
