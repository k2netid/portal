import { test, expect } from '@playwright/test';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'ChangeMeOnFirstLogin!';

test.describe('CMS registry → Editorial sidebar', () => {
    test('activating Publishing reveals Editorial in the console sidebar', async ({ page }) => {
        await page.goto('/auth/console-sign-in');
        await page.fill('#email', loginEmail);
        await page.fill('#password', loginPassword);
        await page.locator('button[type="submit"]').click();
        await expect(page).toHaveURL(/\/(?:dash|ja-dash)/, { timeout: 20_000 });

        await page.goto('/dash/extensions');
        await expect(page).toHaveURL(/extensions/, { timeout: 15_000 });

        const cmsTab = page.getByRole('button', { name: /content management system/i });
        if (await cmsTab.isVisible().catch(() => false)) {
            await cmsTab.click();
        }

        const publishingRow = page.locator('tr', { hasText: /publishing/i }).first();
        await expect(publishingRow).toBeVisible({ timeout: 15_000 });

        const activateBtn = publishingRow.getByRole('button', { name: /activate|aktifkan/i });
        if (await activateBtn.isVisible().catch(() => false)) {
            await activateBtn.click();
            const dialogConfirm = page.getByRole('dialog').getByRole('button', { name: /activate|aktifkan/i });
            if (await dialogConfirm.isVisible({ timeout: 5_000 }).catch(() => false)) {
                await dialogConfirm.click();
            }
            await expect(page.getByText(/activated|diaktifkan/i).first()).toBeVisible({ timeout: 20_000 });
        }

        const sidebar = page.locator('[data-testid="console-sidebar-brand"]').locator('xpath=ancestor::*[self::aside or self::nav][1]');
        const navRoot = (await sidebar.count()) > 0 ? sidebar : page.locator('aside, nav').first();

        await expect(navRoot.getByText(/editorial/i).first()).toBeVisible({ timeout: 20_000 });
        await navRoot.getByText(/editorial/i).first().click();
        await expect(navRoot.getByText(/content|konten/i).first()).toBeVisible({ timeout: 10_000 });
    });
});
