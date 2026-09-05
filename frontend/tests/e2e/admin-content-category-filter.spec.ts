import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers/console-e2e';

test.describe('Admin Content Studio - Category Column & Filter E2E', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('displays category column and category filter dropdown in content table', async ({ page }) => {
        await page.goto('/dash/contents');
        await page.waitForLoadState('networkidle');

        // Content Studio header or table should be visible
        const pageHeader = page.locator('h1:has-text("Content Studio"), h1:has-text("Studio Konten")').first();
        await expect(pageHeader).toBeVisible({ timeout: 10000 });

        // Category filter dropdown should exist in toolbar
        const categoryFilterTrigger = page.locator('button[aria-label*="kategori" i], button[aria-label*="category" i]').first();
        await expect(categoryFilterTrigger).toBeVisible({ timeout: 5000 });

        // Table header should contain Category
        const categoryHeader = page.locator('th:has-text("Kategori"), th:has-text("Category")').first();
        await expect(categoryHeader).toBeVisible({ timeout: 5000 });

        // Category badge or text should be present in table rows
        const categoryBadges = page.locator('td button, td div, td span').filter({ hasText: /Prestasi|Guru|Sarana|Kurikulum|Tanpa Kategori|Uncategorized/i });
        await expect(categoryBadges.first()).toBeVisible({ timeout: 5000 });

        // Click category filter to open dropdown
        await categoryFilterTrigger.click();

        // Check options: All Categories, Uncategorized
        const allOption = page.locator('[role="option"]:has-text("All categories"), [role="option"]:has-text("Semua kategori"), [role="option"]:has-text("Sadaya kategori")').first();
        await expect(allOption).toBeVisible({ timeout: 5000 });

        const uncatOption = page.locator('[role="option"]:has-text("Uncategorized"), [role="option"]:has-text("Tanpa kategori")').first();
        await expect(uncatOption).toBeVisible({ timeout: 5000 });

        // Select a specific category option (e.g. Prestasi if present)
        const categoryOption = page.locator('[role="option"]').filter({ hasText: /Prestasi/i }).first();
        if (await categoryOption.isVisible()) {
            await categoryOption.click();
            await page.waitForLoadState('networkidle');

            // Wait for dropdown to close
            await page.waitForTimeout(400);

            // Reset button should now be visible
            const resetBtn = page.locator('button:has-text("Reset"), button[title*="Reset" i]').first();
            await expect(resetBtn).toBeVisible({ timeout: 5000 });

            // Click reset
            await resetBtn.click({ force: true });
            await page.waitForLoadState('networkidle');
        } else {
            // Close select by pressing escape
            await page.keyboard.press('Escape');
        }
    });

    test('clicking a category badge in row filters table by that category', async ({ page }) => {
        await page.goto('/dash/contents');
        await page.waitForLoadState('networkidle');

        // Find a clickable category badge in the table
        const clickableBadge = page.locator('td .cursor-pointer').filter({ hasText: /Prestasi|Guru|Sarana|Kurikulum/i }).first();
        if (await clickableBadge.isVisible()) {
            const badgeText = (await clickableBadge.textContent())?.trim() ?? '';
            await clickableBadge.click();
            await page.waitForLoadState('networkidle');

            // Reset button should now appear
            const resetBtn = page.locator('button:has-text("Reset"), button[title*="Reset" i]').first();
            await expect(resetBtn).toBeVisible({ timeout: 5000 });

            // The filter dropdown should now reflect the clicked category
            const categoryFilterTrigger = page.locator('button[aria-label*="kategori" i], button[aria-label*="category" i]').first();
            await expect(categoryFilterTrigger).toContainText(badgeText);
        }
    });
});
