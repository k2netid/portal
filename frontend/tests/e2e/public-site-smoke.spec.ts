import { expect, test } from '@playwright/test';

test.describe('public /site theme runtime', () => {
    test('home renders zenith shell without theme resolver crash', async ({ page }) => {
        await page.goto('/site/');

        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('header').first()).toBeVisible({ timeout: 15000 });
        await expect(page.locator('h1').first()).toBeVisible({ timeout: 15000 });
    });
});
