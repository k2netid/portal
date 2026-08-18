import { expect, test } from '@playwright/test';

const themeJson = {
    success: true,
    message: 'ok',
    data: {
        id: "1",
        name: 'Janari',
        slug: 'janari',
        type: 'frontend',
        path: 'janari',
        version: '1.0.0',
        settings: {
            enable_contact: true,
            animation_enabled: false,
        },
        custom_css: '',
        assets: { css: [] as string[], js: [] as string[] },
        manifest: { name: 'Janari' },
    },
};

const publicSettingsJson = {
    success: true,
    message: 'ok',
    data: {
        site_name: 'E2E Site',
    },
};

const contactContentJson = {
    success: true,
    message: 'ok',
    data: {
        title: 'Contact',
        body: '',
    },
};

const contactFormJson = {
    success: true,
    message: 'ok',
    data: {
        id: "1",
        slug: 'contact',
        name: 'Contact',
        description: null,
        settings: { captcha_required: false },
        fields: [] as unknown[],
    },
};

test.describe('Public theme + contact navigation', () => {
    test.beforeEach(async ({ page }) => {
        const fulfillJson = (body: unknown) => ({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify(body),
        });

        await page.route('**/api/v1/public/layout/themes/active**', async (route) => {
            await route.fulfill(fulfillJson(themeJson));
        });

        await page.route('**/api/v1/public/system/settings**', async (route) => {
            await route.fulfill(fulfillJson(publicSettingsJson));
        });

        await page.route('**/api/v1/public/publishing/contents/contact**', async (route) => {
            if (route.request().method() !== 'GET') {
                await route.continue();

                return;
            }
            await route.fulfill(fulfillJson(contactContentJson));
        });

        await page.route('**/api/v1/public/forms/contact**', async (route) => {
            const method = route.request().method();
            if (method === 'GET') {
                await route.fulfill(fulfillJson(contactFormJson));

                return;
            }
            if (method === 'POST' && route.request().url().includes('/track')) {
                await route.fulfill({ status: 204, body: '' });

                return;
            }
            await route.fulfill({ status: 404, body: '{}' });
        });
    });

    test('home → contact → home → contact stays stable', async ({ page }) => {
        await page.goto('/');
        await expect(page).toHaveURL(/\/$/);

        await page.goto('/contact');
        await expect(page).toHaveURL(/\/contact$/);
        await expect(page.getByRole('main').first()).toBeVisible({ timeout: 15_000 });
        await expect(page.getByRole('heading', { level: 1 }).first()).toBeVisible();

        await page.goto('/');
        await expect(page).toHaveURL(/\/$/);

        await page.goto('/contact');
        await expect(page).toHaveURL(/\/contact$/);
        await expect(page.getByRole('main').first()).toBeVisible({ timeout: 15_000 });
        await expect(page.getByRole('heading', { level: 1 }).first()).toBeVisible();
    });
});
