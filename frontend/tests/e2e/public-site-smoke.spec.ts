import { expect, test } from '@playwright/test';

test.describe('public /site theme runtime', () => {
    test('home renders zenith shell without theme resolver crash', async ({ page }) => {
        await page.goto('/site/');

        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('header').first()).toBeVisible({ timeout: 15000 });
        await expect(page.locator('h1').first()).toBeVisible({ timeout: 15000 });
    });

    test('contact page still exposes a public form', async ({ page }) => {
        await page.goto('/site/contact');
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('form').first()).toBeVisible({ timeout: 15000 });
        await expect(page.locator('button[type="submit"]').first()).toBeVisible();
    });

    test('header marketing pages do not 404', async ({ page }) => {
        for (const path of ['/site/solusi', '/site/services', '/site/pricing', '/site/career', '/site/achievement', '/site/blog']) {
            await page.goto(path);
            await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
            await expect(page).not.toHaveURL(/\/404/);
            await expect(page.locator('h1').first()).toBeVisible({ timeout: 15000 });
        }
    });

    test('unknown public path stays on the /site 404 shell', async ({ page }) => {
        await page.goto('/site/this-page-does-not-exist-xyz');
        await expect(page).toHaveURL(/\/site\/404/);
        await expect(page.getByRole('heading', { name: '404' })).toBeVisible({ timeout: 15000 });
    });

    test('member login is a public form, not console IAM', async ({ page }) => {
        await page.goto('/site/member/login');
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('input[type="email"]').first()).toBeVisible();
        await expect(page.getByRole('link', { name: /create one/i }).first()).toBeVisible();
    });

    test('search page is reachable from the public shell', async ({ page }) => {
        await page.goto('/site/search');
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('h1').first()).toBeVisible({ timeout: 15000 });
        await expect(page.locator('input[type="search"]').first()).toBeVisible();
    });

    test('member register is a public form', async ({ page }) => {
        await page.goto('/site/member/register');
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('input[autocomplete="name"]').first()).toBeVisible();
        await expect(page.getByRole('link', { name: /sign in/i }).first()).toBeVisible();
    });

    test('guest account route redirects to member login', async ({ page }) => {
        await page.goto('/site/member/account');
        await expect(page).toHaveURL(/\/site\/member\/login/);
    });

    test('public 404 home stays on /site', async ({ page }) => {
        await page.goto('/site/this-page-does-not-exist-xyz');
        await expect(page).toHaveURL(/\/site\/404/);
        await page.getByRole('button').first().click();
        await expect(page).toHaveURL(/\/site\/?$/);
        await expect(page.locator('header').first()).toBeVisible({ timeout: 15000 });
    });
});
