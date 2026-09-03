import { expect, test } from '@playwright/test';

test.describe('Auth And Probe Routing Guard', () => {
    test('legacy public auth paths resolve to not-found', async ({ page }) => {
        await page.goto('/login');
        await expect(page).toHaveURL(/\/404$/);

        await page.goto('/register');
        await expect(page).toHaveURL(/\/404$/);
    });

    test('common probe paths resolve to not-found', async ({ page }) => {
        const probePaths = ['/admin', '/dashboard', '/panel', '/wp-admin', '/phpmyadmin'];

        for (const probePath of probePaths) {
            const res = await page.request.get(probePath, { maxRedirects: 0 });
            if (res.status() >= 300 && res.status() < 400) {
                expect(res.headers()['location']).toMatch(/\/404$/);
            } else {
                await page.goto(probePath);
                await expect(page).toHaveURL(/\/404$/);
            }
        }
    });

    test('encoded and case-variant probe paths resolve to not-found', async ({ page }) => {
        const probePaths = ['/%61dmin', '/AdMiN', '/%2Fadmin', '/DaShBoArD'];

        for (const probePath of probePaths) {
            const res = await page.request.get(probePath, { maxRedirects: 0 });
            if (res.status() >= 300 && res.status() < 400) {
                expect(res.headers()['location']).toMatch(/\/404$/);
            } else {
                await page.goto(probePath);
                await expect(page).toHaveURL(/\/404$/);
            }
        }
    });

    test('protected dashboard route resolves to not-found for guest', async ({ page }) => {
        await page.goto('/dash');
        await expect(page).toHaveURL(/\/404$/);
    });

    test('reserved scanner slugs resolve to not-found on public site', async ({ page }) => {
        for (const path of ['/system', '/random-probe-xyz-not-a-page']) {
            const res = await page.request.get(path, { maxRedirects: 0 });
            if (res.status() >= 300 && res.status() < 400) {
                expect(res.headers()['location']).toMatch(/\/404$/);
            } else {
                await page.goto(path);
                await expect(page).toHaveURL(/\/404$/);
            }
        }
    });

    test('security login slug remains accessible', async ({ page }) => {
        await page.goto('/auth/console-sign-in');
        await expect(page).toHaveURL(/\/auth\/console-sign-in$/);
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
    });
});
