import { test, expect } from '@playwright/test';

test.describe('Universal Widget Catalog & Post Sidebar WidgetArea', () => {
    test('post detail page displays widget area with search and categories', async ({ page }) => {
        await page.goto('/blog/sarangenge-sample-ppdb-2026');
        await page.waitForLoadState('networkidle');

        // Check that WidgetArea exists with location="sidebar"
        const widgetArea = page.locator('.widget-area[data-widget-location="sidebar"], aside.space-y-6, aside.space-y-8').first();
        await expect(widgetArea).toBeVisible({ timeout: 15000 });

        // Check for Universal SearchWidget presence
        const searchInput = page.locator('.search-widget input, input[type="text"][placeholder*="kata kunci"], input[placeholder*="search" i]').first();
        await expect(searchInput).toBeVisible({ timeout: 10000 });

        // Check for CategoriesWidget presence
        const categoriesHeader = page.locator('text=/Kategori Berita|Article Categories|Kategori Warta/i').first();
        await expect(categoriesHeader).toBeVisible({ timeout: 10000 });

        // Test typing into search input
        await searchInput.fill('PPDB');
        await expect(searchInput).toHaveValue('PPDB');
    });

    test('blog index page displays universal search and interactive categories', async ({ page }) => {
        await page.goto('/blog');
        await page.waitForLoadState('networkidle');

        // Verify SearchWidget input is visible
        const searchInput = page.locator('.search-widget input').or(page.locator('input[placeholder*="kata kunci"]')).first();
        await expect(searchInput).toBeVisible({ timeout: 15000 });

        // Verify CategoriesWidget list is populated
        const categoryWidget = page.locator('.categories-widget').or(page.locator('text=/Kategori Berita|Article Categories/i')).first();
        await expect(categoryWidget).toBeVisible({ timeout: 10000 });
    });
});
