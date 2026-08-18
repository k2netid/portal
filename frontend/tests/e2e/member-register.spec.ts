import { test, expect } from '@playwright/test';

const subscriptionDomain = process.env.E2E_SUBSCRIPTION_DOMAIN ?? 'demo.jejakawan.com';
const memberPassword = process.env.E2E_MEMBER_PASSWORD ?? 'Password123!';

test.describe('Member self-service registration', () => {
  test('registers and lands on member dashboard', async ({ page }) => {
    const unique = Date.now();
    const email = `e2e-member-${unique}@academy.test`;
    const name = `E2E Member ${unique}`;

    await page.goto('/member/register');
    await expect(page.getByRole('heading', { name: /Create member account|Buat akun anggota/i })).toBeVisible();

    await page.fill('#subscription-domain', subscriptionDomain);
    await expect(page.getByRole('button', { name: /Register|Daftar/i })).toBeEnabled({ timeout: 15_000 });

    await page.fill('#name', name);
    await page.fill('#email', email);
    await page.fill('#password', memberPassword);
    await page.fill('#password_confirmation', memberPassword);
    await page.getByRole('button', { name: /Register|Daftar/i }).click();

    await expect(page).toHaveURL(/\/member\/?$/, { timeout: 15_000 });
    await expect(page.getByText(email)).toBeVisible();
  });
});
