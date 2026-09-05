import { expect, test } from '@playwright/test';

test.describe('Layung public theme honesty and drawer', () => {
    test.use({
        locale: 'id-ID',
        extraHTTPHeaders: {
            'X-E2E-Captcha-Bypass': process.env.E2E_CAPTCHA_BYPASS_TOKEN || 'local-e2e',
        },
    });

    test('pricing/isp hydrates without third-party benchmark or fake school claims', async ({ page }) => {
        await page.goto('/pricing/isp');
        await expect(page.locator('h1').first()).toBeVisible({ timeout: 20_000 });
        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0);

        const body = await page.locator('body').innerText();
        expect(body).not.toMatch(/Ookla|Cisco SBA|ITU-T Y\.1541|SMP Negeri/i);
        await expect(page.getByText(/estimasi internal|internal portal estimate/i).first()).toBeVisible();
        await expect(page.getByText(/indikasi|indicative/i).first()).toBeVisible();
    });

    test('mobile drawer closes on Escape and restores hamburger', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/');
        await expect(page.locator('header').first()).toBeVisible({ timeout: 20_000 });

        const openMenu = page.getByRole('button', { name: /buka menu|open menu/i });
        await expect(openMenu).toBeVisible();
        await openMenu.click();

        const drawer = page.locator('#layung-mobile-drawer');
        await expect(drawer).toBeVisible();
        await expect(drawer).toHaveAttribute('aria-modal', 'true');

        await drawer.press('Escape');
        await expect(drawer).toHaveCount(0);
        await expect(page.getByRole('button', { name: /buka menu|open menu/i })).toBeVisible();
    });
});
