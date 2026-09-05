import { test, expect } from '@playwright/test';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'password';

test.describe('Extensions & Integrations Management', () => {
    test('floating social dock and instagram feed appear under Plugin category tab', async ({ page }) => {
        // Sign in
        await page.goto('/auth/console-sign-in');
        await page.fill('#email', loginEmail);
        await page.fill('#password', loginPassword);
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/(?:dash|ja-dash)/, { timeout: 15000 });

        // Navigate to Extensions page
        await page.goto('/dash/extensions');
        await expect(page.getByRole('heading', { name: /Ekstensi|Extensions|App Store/i }).first()).toBeVisible({ timeout: 15000 });

        // Click the Plugin tab
        const pluginTab = page.getByRole('button', { name: /^Plugins?$/i }).first();
        await expect(pluginTab).toBeVisible();
        await pluginTab.click();

        // Verify Floating Social Dock & Hotline appears under Plugin tab
        const dockCard = page.locator('text=Floating Social Dock & Hotline').first();
        await expect(dockCard).toBeVisible({ timeout: 5000 });

        // Verify Instagram Feed Integration also appears under Plugin tab
        const igCard = page.locator('text=Instagram Feed Integration').first();
        await expect(igCard).toBeVisible({ timeout: 5000 });

        // Take a screenshot proving both plugins appear under the Plugin tab
        await page.screenshot({
            path: '/home/jejakawan/.gemini/antigravity-ide/brain/7f4d6cac-ab1a-4a60-b3c6-276c296911ef/extensions_plugin_tab_verified.png',
            fullPage: false
        });
    });
});
