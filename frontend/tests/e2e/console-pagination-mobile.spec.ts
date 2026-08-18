import { test, expect, type Page } from '@playwright/test';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'ChangeMeOnFirstLogin!';

async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/auth/console-sign-in');
    await page.fill('#email', loginEmail);
    await page.fill('#password', loginPassword);
    const submitBtn = page.locator('button[type="submit"]');
    await expect(submitBtn).toBeEnabled();
    await submitBtn.click();
    await expect(page).toHaveURL(/\/dash/, { timeout: 15000 });
}

test.describe('Console pagination — mobile layout', () => {
    test.beforeEach(async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await loginAsAdmin(page);
    });

    test('security logs pagination fits viewport and shows compact summary', async ({ page }) => {
        await page.goto('/dash/security-journal');
        await expect(
            page.getByText(/Security Maintenance Mode|Mode Pemeliharaan Keamanan/i),
        ).toBeVisible({ timeout: 15000 });

        const pagination = page.locator('[data-slot="console-pagination"]');
        await expect(pagination).toBeVisible();

        await expect(pagination).toContainText(/\d+[\u2013-]\d+\s*\/\s*\d+/);

        const fits = await page.evaluate(() => {
            const wrap = document.querySelector('.console-content-wrap');
            const bar = document.querySelector('[data-slot="console-pagination"]');
            if (!wrap || !bar) return false;
            const w = wrap.getBoundingClientRect();
            const b = bar.getBoundingClientRect();
            return b.left >= w.left - 1 && b.right <= w.right + 1;
        });
        expect(fits).toBe(true);
    });
});
