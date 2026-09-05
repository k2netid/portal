import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers/console-e2e';

test.describe('Unified Theme and Extension Package Lifecycle & Settings', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('themes page displays upload button and theme cards have export action', async ({ page }) => {
        await page.goto('/dash/themes');
        await page.waitForLoadState('networkidle');

        // Check for Unggah / Upload button
        const uploadBtn = page.locator('button:has-text("Unggah"), button:has-text("Upload")').first();
        await expect(uploadBtn).toBeVisible({ timeout: 10000 });

        // Check that theme cards exist
        const themeCards = page.locator('.grid > div');
        await expect(themeCards.first()).toBeVisible({ timeout: 10000 });

        // Check that theme card actions include export button
        const exportAction = page.locator('button[title*="Ekspor"], button[title*="Export"], button[aria-label*="Ekspor"], button[aria-label*="Export"]').first();
        await expect(exportAction).toBeVisible();
    });

    test('extensions page displays upload button and rows have export action', async ({ page }) => {
        await page.goto('/dash/extensions');
        await page.waitForLoadState('networkidle');

        // Check for Unggah / Upload button
        const uploadBtn = page.locator('button:has-text("Unggah ZIP"), button:has-text("Upload ZIP")').first();
        await expect(uploadBtn).toBeVisible({ timeout: 10000 });

        // Check that table rows exist
        const tableRows = page.locator('table tbody tr');
        await expect(tableRows.first()).toBeVisible({ timeout: 10000 });

        // Check for export action button in table
        const exportBtn = page.locator('table tbody tr button:has-text("Ekspor"), table tbody tr button:has-text("Export")').first();
        await expect(exportBtn).toBeVisible();
    });

    test('security settings displays package management group with toggles', async ({ page }) => {
        await page.goto('/dash/settings?tab=security');
        await page.waitForLoadState('networkidle');

        const securityTab = page.locator('button[role="tab"]:has-text("Keamanan"), button[role="tab"]:has-text("Security")');
        if (await securityTab.isVisible()) {
            await securityTab.click();
        }

        // Check for Paket Tema & Ekstensi group
        const groupTitle = page.locator('text=/Paket Tema & Ekstensi|Themes & Extensions Packages|Pakét Téma & Ekstensi/i').first();
        await expect(groupTitle).toBeVisible({ timeout: 10000 });

        // Check for toggle switches for upload/export
        const uploadToggle = page.locator('text=/Unggah \\/ Impor Tema|Upload \\/ Import Themes/i').first();
        await expect(uploadToggle).toBeVisible();

        const exportToggle = page.locator('text=/Ekspor Paket Tema|Export Theme Packages/i').first();
        await expect(exportToggle).toBeVisible();
    });

    test('license settings tab includes custom package capabilities', async ({ page }) => {
        await page.goto('/dash/settings?tab=license');
        await page.waitForLoadState('networkidle');

        const licenseTab = page.locator('button[role="tab"]:has-text("Lisensi"), button[role="tab"]:has-text("License")');
        if (await licenseTab.isVisible()) {
            await licenseTab.click();
        }

        // Check for feature definitions
        const themeImportCap = page.locator('text=/Custom Theme Import/i').first();
        await expect(themeImportCap).toBeVisible({ timeout: 10000 });

        const pluginImportCap = page.locator('text=/Custom Plugin\\/Extension Import/i').first();
        await expect(pluginImportCap).toBeVisible();
    });
});
