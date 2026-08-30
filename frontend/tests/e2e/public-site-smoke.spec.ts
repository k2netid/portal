import { expect, test } from '@playwright/test';

test.describe('public apex theme runtime', () => {
    test('home renders zenith shell without theme resolver crash', async ({ page }) => {
        await page.goto('/');

        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('header').first()).toBeVisible({ timeout: 15000 });
        await expect(page.locator('h1').first()).toBeVisible({ timeout: 15000 });
    });

    test('legacy /site prefix redirects to apex', async ({ page }) => {
        await page.goto('/site/contact');
        await expect(page).toHaveURL(/\/contact\/?$/);
        await expect(page.locator('form').first()).toBeVisible({ timeout: 15000 });
    });

    test('contact page still exposes a public form', async ({ page }) => {
        await page.goto('/contact');
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('form').first()).toBeVisible({ timeout: 15000 });
        await expect(page.locator('button[type="submit"]').first()).toBeVisible();
    });

    test('header marketing pages do not 404', async ({ page }) => {
        for (const path of ['/solusi', '/services', '/pricing', '/career', '/achievement', '/blog']) {
            await page.goto(path);
            await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
            await expect(page).not.toHaveURL(/\/404/);
            await expect(page.locator('h1').first()).toBeVisible({ timeout: 15000 });
        }
    });

    test('unknown public path stays on the apex 404 shell', async ({ page }) => {
        await page.goto('/this-page-does-not-exist-xyz');
        await expect(page).toHaveURL(/\/404/);
        await expect(page.getByRole('heading', { name: '404' })).toBeVisible({ timeout: 15000 });
    });

    test('member login is a public form, not console IAM', async ({ page }) => {
        await page.goto('/member/login');
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('input[type="email"]').first()).toBeVisible();
        await expect(page.getByRole('link', { name: /create one/i }).first()).toBeVisible();
    });

    test('search page is reachable from the public shell', async ({ page }) => {
        await page.goto('/search');
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('h1').first()).toBeVisible({ timeout: 15000 });
        await expect(page.locator('input[type="search"]').first()).toBeVisible();
    });

    test('member register is a public form', async ({ page }) => {
        await page.goto('/member/register');
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('input[autocomplete="name"]').first()).toBeVisible();
        await expect(page.getByRole('link', { name: /sign in/i }).first()).toBeVisible();
    });

    test('member verified result page is public', async ({ page }) => {
        await page.goto('/member/verified?status=ok');
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.getByRole('heading').first()).toBeVisible({ timeout: 15000 });
    });

    test('guest account route redirects to member login', async ({ page }) => {
        await page.goto('/member/account');
        await expect(page).toHaveURL(/\/member\/login/);
    });

    test('public 404 home returns to apex', async ({ page }) => {
        await page.goto('/this-page-does-not-exist-xyz');
        await expect(page).toHaveURL(/\/404/);
        await page.getByRole('button').first().click();
        await expect(page).toHaveURL(/\/?$/);
        await expect(page.locator('header').first()).toBeVisible({ timeout: 15000 });
    });

    test('console login stays on console shell when site is active', async ({ page }) => {
        await page.goto('/auth/console-sign-in');
        await expect(page.locator('input[name="login"], input[type="text"], input[autocomplete="username"]').first()).toBeVisible({
            timeout: 15000,
        });
        await expect(page.locator('header.janari-header-toolbar, .zenith-theme')).toHaveCount(0);
    });
});
