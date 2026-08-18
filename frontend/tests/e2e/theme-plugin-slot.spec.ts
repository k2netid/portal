import { expect, test } from '@playwright/test';

const fulfillJson = (body: unknown) => ({
  status: 200,
  contentType: 'application/json',
  body: JSON.stringify(body),
});

const themeJson = {
  success: true,
  message: 'ok',
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
    manifest: { name: 'Janari', supports: { janari_canvas: true } },
  },
};

const pluginBlocksJson = {
  success: true,
  message: 'ok',
  data: {
    plugins: [
      { slug: 'content-share-bar', priority: 20, slots: ['after_post_content'] },
    ],
    slots: ['after_header', 'before_footer', 'after_post_content'],
  },
};

const postJson = {
  success: true,
  message: 'ok',
  data: {
    id: 'post-1',
    slug: 'e2e-sample-post',
    type: 'post',
    title: 'E2E Sample Post',
    body: '<p>Hello from e2e.</p>',
    published_at: '2026-01-01',
    category: { name: 'News' },
    author: { name: 'Editor' },
    tags: [] as unknown[],
  },
};

test.describe('Janari plugin app block slot', () => {
  test.beforeEach(async ({ page }) => {
    await page.route('**/api/v1/public/layout/themes/active**', (route) =>
      route.fulfill(fulfillJson(themeJson)),
    );
    await page.route('**/api/v1/public/layout/plugin-blocks**', (route) =>
      route.fulfill(fulfillJson(pluginBlocksJson)),
    );
    await page.route('**/api/v1/public/system/settings**', (route) =>
      route.fulfill(fulfillJson({ success: true, data: { site_name: 'E2E' } })),
    );
    await page.route('**/api/v1/public/publishing/contents/e2e-sample-post**', (route) => {
      if (route.request().method() !== 'GET') return route.continue();
      return route.fulfill(fulfillJson(postJson));
    });
    await page.route('**/api/v1/public/layout/menus/**', (route) =>
      route.fulfill(fulfillJson({ success: true, data: [] })),
    );
  });

  test('renders content-share-bar below post body when plugin manifest active', async ({ page }) => {
    const pluginBlocksReady = page.waitForResponse('**/api/v1/public/layout/plugin-blocks**');
    const postReady = page.waitForResponse('**/api/v1/public/publishing/contents/e2e-sample-post**');
    await page.goto('/blog/e2e-sample-post');
    await Promise.all([pluginBlocksReady, postReady]);

    const article = page.locator('article').first();
    await expect(article).toBeVisible({ timeout: 15_000 });
    await expect(article.getByRole('heading', { level: 1, name: 'E2E Sample Post' })).toBeVisible();
    await expect(article.getByText(/Bagikan artikel ini/i)).toBeVisible({ timeout: 10_000 });
  });
});
