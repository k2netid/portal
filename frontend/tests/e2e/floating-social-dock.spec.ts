import { expect, test } from '@playwright/test';

test.describe('Floating Social Dock Plugin', () => {
    test('dock renders on public portal and displays social links', async ({ page }) => {
        await page.goto('/');

        // Locate official floating dock block
        const dock = page.locator('.ja-floating-social-dock');
        await expect(dock).toBeAttached({ timeout: 15000 });

        // Locate dock trigger button
        const dockTrigger = page.getByRole('button', { name: /Media Sosial & Hotline|Social Media & Hotline|Buka Media Sosial/i }).first();
        await expect(dockTrigger).toBeVisible({ timeout: 15000 });

        // Click trigger button to expand dock
        await dockTrigger.click();

        // Verify dock body is expanded
        const dockBody = page.locator('.ja-floating-social-dock__body');
        await expect(dockBody).toBeVisible({ timeout: 5000 });

        // Verify social links exist inside dock
        const links = page.locator('.ja-floating-social-dock__item');
        const count = await links.count();
        expect(count).toBeGreaterThan(0);

        // Take a screenshot of the expanded dock
        await page.screenshot({
            path: '/home/jejakawan/.gemini/antigravity-ide/brain/7f4d6cac-ab1a-4a60-b3c6-276c296911ef/floating_dock_verified.png',
            fullPage: false
        });
    });
});
