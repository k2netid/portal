import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers/console-e2e';

test.describe('Widget Modal Dropdown & Creation E2E', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('select dropdown inside widget modal opens and selects type', async ({ page }) => {
        await page.goto('/dash/widgets');
        await page.waitForLoadState('networkidle');

        // Click + New Widget button
        const newBtn = page.locator('button:has-text("New Widget"), button:has-text("Widget Baru")').first();
        await expect(newBtn).toBeVisible({ timeout: 10000 });
        await newBtn.click();

        // Check modal visibility
        const modal = page.locator('[role="dialog"]').first();
        await expect(modal).toBeVisible({ timeout: 5000 });

        // Click SelectTrigger for type
        const selectTrigger = modal.locator('[role="combobox"]').first();
        await expect(selectTrigger).toBeVisible();
        await selectTrigger.click();

        // The SelectContent should now be visible in front of modal
        const searchOption = page.locator('[role="option"]:has-text("Pencarian"), [role="option"]:has-text("Search")').first();
        await expect(searchOption).toBeVisible({ timeout: 5000 });

        // Click Search option
        await searchOption.click();

        // Verify that Title was auto-filled with default
        const titleInput = modal.locator('input[type="text"]').first();
        await expect(titleInput).toHaveValue(/Cari Warta|Pencarian|Search/i);

        // Verify Location is preset to sidebar
        const locationInput = modal.locator('input[placeholder="sidebar"]');
        await expect(locationInput).toHaveValue('sidebar');

        // Submit form
        const createBtn = modal.locator('button:has-text("Create"), button:has-text("Buat")').first();
        await expect(createBtn).toBeEnabled();
        await createBtn.click();

        // Modal should close upon successful creation
        await expect(modal).not.toBeVisible({ timeout: 10000 });

        // Verify widget appears in the table
        const tableRow = page.locator('table tbody tr:has-text("sidebar")').first();
        await expect(tableRow).toBeVisible({ timeout: 10000 });
    });
});
