import { expect, test } from '@playwright/test';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'ChangeMeOnFirstLogin!';

test.describe('Hub onboarding wizard', () => {
  test('dashboard shows checklist and dismiss hides it', async ({ page }) => {
    await page.addInitScript(() => {
      window.__FORCE_ONBOARDING__ = true;
    });

    await page.goto('/auth/console-sign-in');
    await page.fill('#email', loginEmail);
    await page.fill('#password', loginPassword);
    await expect(page.locator('button[type="submit"]')).toBeEnabled();
    await page.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/\/(?:dash|ja-dash)(?:\/dashboard)?(?:\/|$|\?)/, { timeout: 15_000 });

    const wizard = page.getByTestId('hub-onboarding-wizard');
    await expect(wizard).toBeVisible({ timeout: 15_000 });
    await expect(wizard.getByText(/Site identity|System Identity|Identitas/i)).toBeVisible();

    await page.getByTestId('hub-onboarding-dismiss').click();
    await expect(wizard).toBeHidden({ timeout: 10_000 });
  });
});
