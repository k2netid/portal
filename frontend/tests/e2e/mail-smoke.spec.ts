import { test, expect } from '@playwright/test';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'ChangeMeOnFirstLogin!';

test.describe('JA-Mail console smoke', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/auth/console-sign-in');
        await page.fill('#email', loginEmail);
        await page.fill('#password', loginPassword);
        await page.locator('button[type="submit"]').click();
        await expect(page).toHaveURL(/.*dash/, { timeout: 15000 });
    });

    test('mail route loads inbox shell', async ({ page }) => {
        await page.goto('/dash/mail');
        await expect(page).toHaveURL(/\/dash\/mail/, { timeout: 15000 });

        const mailHeading = page.getByRole('heading', { level: 1 });
        await expect(mailHeading).toBeVisible({ timeout: 15000 });

        const composeButton = page.getByRole('button', { name: /compose|tulis|tulis surat/i }).first();
        await expect(composeButton).toBeVisible({ timeout: 10000 });
    });
});
