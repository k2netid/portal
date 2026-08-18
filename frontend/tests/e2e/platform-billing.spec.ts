import { test, expect } from '@playwright/test';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'ChangeMeOnFirstLogin!';

async function loginAsSuperAdmin(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/auth/console-sign-in');
    await page.fill('#email', loginEmail);
    await page.fill('#password', loginPassword);
    await page.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/.*dash/, { timeout: 15000 });
}

test.describe('Platform billing console', () => {
    test('super admin can open platform billing tab', async ({ page }) => {
        await loginAsSuperAdmin(page);

        await page.goto('/dash/platform');
        await expect(page.getByRole('heading', { name: /Platform console|Konsol platform/i })).toBeVisible({ timeout: 10000 });

        await page.getByRole('button', { name: /Billing|Penagihan|Tagihan/i }).click();
        await expect(page.getByText(/Billing \/ Transactions|Penagihan \/ Transaksi|Tagihan \/ Transaksi/i)).toBeVisible({ timeout: 10000 });
    });
});
