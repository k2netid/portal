import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers/console-e2e';

test.describe('Categories Menu Consolidation & General Tags E2E', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('sidebar has no standalone Categories menu and shows General Tags', async ({ page }) => {
        await page.goto('/dash');
        await page.waitForLoadState('networkidle');

        // Verify sidebar exists
        const sidebar = page.locator('aside, nav[aria-label="Sidebar"], [data-testid="console-sidebar"]').first();
        await expect(sidebar).toBeVisible({ timeout: 10000 });

        // Standalone "Categories" / "Kategori" should NOT exist in sidebar
        const categoriesMenuItem = sidebar.locator('a[href*="/categories"], button:has-text("Categories"), button:has-text("Kategori")');
        await expect(categoriesMenuItem).toHaveCount(0);

        // If Library group is collapsed, expand it
        const libraryGroup = sidebar.locator('button:has-text("Library"), button:has-text("Pustaka")').first();
        if (await libraryGroup.isVisible()) {
            await libraryGroup.click();
        }

        // "General Tags" / "Tag Umum" should exist in sidebar under Library
        const generalTagsMenuItem = sidebar.locator('text=/General Tags|Tag Umum/i').first();
        await expect(generalTagsMenuItem).toBeVisible({ timeout: 5000 });
    });

    test('accessing /categories redirects cleanly to /contents?tab=categories', async ({ page }) => {
        await page.goto('/dash/categories');
        await page.waitForLoadState('networkidle');

        // URL should have redirected to tab=categories
        await expect(page).toHaveURL(/.*\/contents\?tab=categories/);

        // Content Studio header should be visible
        const pageHeader = page.locator('h1:has-text("Content Studio"), h1:has-text("Studio Konten")').first();
        await expect(pageHeader).toBeVisible({ timeout: 10000 });

        // Categories tab trigger should be active (data-state="active")
        const categoriesTab = page.locator('[role="tab"]:has-text("Categories"), [role="tab"]:has-text("Kategori")').first();
        await expect(categoriesTab).toBeVisible({ timeout: 5000 });
        await expect(categoriesTab).toHaveAttribute('data-state', 'active');

        // Category search bar or table should be visible
        const categorySearch = page.locator('input[placeholder*="Cari kategori"], input[placeholder*="Search categor"]').first();
        await expect(categorySearch).toBeVisible({ timeout: 5000 });
    });
});
