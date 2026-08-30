import { test, expect } from '@playwright/test';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'ChangeMeOnFirstLogin!';

test.describe('Authentication Flow', () => {
    test('should login successfully as super admin (seed)', async ({ page }) => {
        await page.goto('/auth/console-sign-in');

        await page.fill('#email', loginEmail);
        await page.fill('#password', loginPassword);

        const submitBtn = page.locator('button[type="submit"]');
        await expect(submitBtn).toBeEnabled();
        await submitBtn.click();

        await expect(page).toHaveURL(/\/(?:dash|ja-dash)(?:\/dashboard)?(?:\/|$|\?)/);
        await expect(page).not.toHaveURL(/\/419/);
        await expect(page.locator('[data-testid="console-sidebar-brand"]')).toBeVisible({ timeout: 15_000 });
        await page.waitForTimeout(2500);
        await expect(page).not.toHaveURL(/\/419/);
    });

    test('should show error for invalid credentials', async ({ page }) => {
        await page.goto('/auth/console-sign-in');

        await page.fill('#email', 'wrong@example.com');
        await page.fill('#password', 'wrongpassword');

        await page.click('button[type="submit"]');

        const errorMsg = page.locator('text=/invalid|wrong|gagal|credential|failed|salah/i').first();
        await expect(errorMsg).toBeVisible();
    });
});
