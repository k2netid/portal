import { expect, test, type Page } from '@playwright/test';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'password';

async function consoleLogin(page: Page): Promise<void> {
    await page.goto('/auth/console-sign-in');
    await page.fill('#email', loginEmail);
    await page.fill('#password', loginPassword);
    await page.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/\/(?:dash|ja-dash)/, { timeout: 20_000 });
}

async function confirmDialog(page: Page, confirmName: RegExp): Promise<void> {
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible({ timeout: 10_000 });
    await dialog.getByRole('button', { name: confirmName }).click();
}

test.describe('honesty pass browser flows', () => {

    test('Identity tab loads and persists kernel general site_name', async ({ page }) => {
        await consoleLogin(page);
        await page.goto('/dash/settings?tab=identity');

        const siteName = page.locator('#setting-field-site_name');
        await expect(siteName).toBeVisible({ timeout: 20_000 });

        const original = await siteName.inputValue();
        expect(original.length).toBeGreaterThan(0);

        const token = `Identity ${Date.now()}`;
        await siteName.fill(token);

        const saveBtn = page.getByRole('button', { name: /save settings|simpan pengaturan/i });
        await expect(saveBtn).toBeEnabled();
        await Promise.all([
            page.waitForResponse((res) =>
                res.url().includes('/manage/system/settings/bulk-update') && res.ok(),
            ),
            saveBtn.click(),
        ]);

        await page.goto('/dash/settings?tab=identity');
        await expect(page.locator('#setting-field-site_name')).toHaveValue(token, { timeout: 20_000 });

        await page.locator('#setting-field-site_name').fill(original);
        const restoreSave = page.getByRole('button', { name: /save settings|simpan pengaturan/i });
        await expect(restoreSave).toBeEnabled();
        await Promise.all([
            page.waitForResponse((res) =>
                res.url().includes('/manage/system/settings/bulk-update') && res.ok(),
            ),
            restoreSave.click(),
        ]);
    });

    test('App Store Configure Publishing opens publishing-settings', async ({ page }) => {
        await consoleLogin(page);
        await page.goto('/dash/extensions');
        await expect(page).toHaveURL(/extensions/, { timeout: 15_000 });

        await page.getByRole('button', { name: /content management system/i }).click();
        const publishingRow = page.getByRole('row', { name: /Publishing publishing ·/i });
        await expect(publishingRow).toBeVisible({ timeout: 20_000 });
        await publishingRow.getByRole('button', { name: /configure|konfigurasi/i }).click();

        await expect(page).toHaveURL(/\/dash\/publishing\/settings/, { timeout: 20_000 });
        await expect(page.getByRole('tab', { name: /seo/i })).toBeVisible({ timeout: 15_000 });
        await expect(page.getByRole('tab', { name: /discussion|komentar|comments/i })).toBeVisible();
        await expect(page.locator('#setting-field-site_name')).toHaveCount(0);
    });

    test('activating Janari switches public apex pages and resolves theme i18n', async ({ page }) => {
        test.setTimeout(90_000);
        const i18nErrors: string[] = [];
        page.on('console', (msg) => {
            const text = msg.text();
            if (/Invalid linked format|Message compilation error/i.test(text)) {
                i18nErrors.push(text);
            }
        });

        await consoleLogin(page);
        await page.goto('/dash/themes');
        await expect(page.getByRole('heading', { name: /janari/i }).first()).toBeVisible({ timeout: 20_000 });

        const originalTheme = await page.evaluate(async () => {
            const res = await fetch('/api/v1/public/layout/themes/active', { credentials: 'include' });
            const body = await res.json() as { data?: { slug?: string }; slug?: string };
            return body.data?.slug ?? body.slug ?? 'zenith';
        });

        const janariCard = page.locator('.rounded-xl.border').filter({
            has: page.getByRole('heading', { name: /^Janari$/i }),
        });
        const activateJanari = janariCard.getByRole('button', { name: /activate|aktifkan/i });
        if (await activateJanari.isVisible().catch(() => false)) {
            await activateJanari.click();
            await confirmDialog(page, /activate|aktifkan/i);
            await expect(janariCard.getByText(/active|aktif/i).first()).toBeVisible({ timeout: 20_000 });
        }

        try {
            for (const path of ['/', '/about', '/blog', '/contact']) {
                await page.goto(path);
                await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15_000 });
                await expect(page.locator('.janari-header-toolbar')).toBeVisible({ timeout: 15_000 });
                await expect(page.locator('.zenith-theme')).toHaveCount(0);
                await expect(page.getByText(/theme\.janari\./)).toHaveCount(0);
            }

            await expect(page.locator('body')).not.toContainText('theme.janari.');
            expect(i18nErrors, i18nErrors.join('\n')).toEqual([]);
        } finally {
            if (originalTheme && originalTheme !== 'janari') {
                await page.goto('/dash/themes');
                const originalCard = page.locator('.rounded-xl.border').filter({
                    has: page.getByRole('heading', { name: new RegExp(`^${originalTheme}$`, 'i') }),
                });
                const activateOriginal = originalCard.getByRole('button', { name: /activate|aktifkan/i });
                if (await activateOriginal.isVisible().catch(() => false)) {
                    await activateOriginal.click();
                    await confirmDialog(page, /activate|aktifkan/i);
                }
            }
        }
    });
});
