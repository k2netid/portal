import { test, expect, type Page } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

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

function formatA11yViolations(violations: Awaited<ReturnType<AxeBuilder['analyze']>>['violations']): string {
    if (violations.length === 0) return '';
    return violations
        .map((v) => `${v.impact}: ${v.id} — ${v.help}\n  ${v.nodes.map((n) => n.target.join(', ')).join('\n  ')}`)
        .join('\n\n');
}

async function expectNoSeriousA11yInDialog(page: Page): Promise<void> {
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible({ timeout: 10000 });
    const results = await new AxeBuilder({ page })
        .include('[role="dialog"]')
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();
    const serious = results.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
    expect(serious, formatA11yViolations(serious)).toEqual([]);
}

test.describe('Console modal — WCAG axe gate (P0)', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('backups schedule modal', async ({ page }) => {
        await page.goto('/dash/backups');
        await expect(page.getByRole('heading', { level: 1 })).toBeVisible({ timeout: 15000 });
        await page.getByRole('button', { name: /configure|atur|schedule|jadwal/i }).click();
        await expectNoSeriousA11yInDialog(page);
        await page.keyboard.press('Escape');
    });

    test('scheduled tasks create modal', async ({ page }) => {
        await page.goto('/dash/scheduled-tasks');
        await expect(page.getByRole('heading', { level: 1 })).toBeVisible({ timeout: 15000 });
        await page.getByRole('button', { name: /scheduled task|tugas terjadwal|create task/i }).click();
        await expectNoSeriousA11yInDialog(page);
        await page.keyboard.press('Escape');
    });

    test('categories form modal', async ({ page }) => {
        await page.goto('/dash/categories');
        await expect(page.getByRole('heading', { level: 1 })).toBeVisible({ timeout: 15000 });
        await page.getByRole('button', { name: /create new|buat baru|new category|kategori baru/i }).first().click();
        await expectNoSeriousA11yInDialog(page);
        await page.keyboard.press('Escape');
    });

    test('media upload modal', async ({ page }) => {
        await page.goto('/dash/media');
        await page.getByRole('button', { name: /upload|unggah/i }).first().click();
        await expectNoSeriousA11yInDialog(page);
        await page.keyboard.press('Escape');
    });

    test('platform package edit modal', async ({ page }) => {
        await page.goto('/dash/platform');
        await expect(page.getByRole('heading', { level: 1 })).toBeVisible({ timeout: 15000 });
        await page.getByRole('button', { name: /packages|paket/i }).click();
        const pkgRows = page.locator('table tbody tr');
        if ((await pkgRows.count()) === 0) {
            test.skip(true, 'no packages in DB');
            return;
        }
        const editBtn = page.getByRole('button', { name: /edit features|ubah fitur|fitur|features/i }).first();
        if ((await editBtn.count()) === 0) {
            test.skip(true, 'no packages seeded');
            return;
        }
        await editBtn.click();
        await expectNoSeriousA11yInDialog(page);
        await page.keyboard.press('Escape');
    });

    test('security file integrity resync modal', async ({ page }) => {
        await page.goto('/dash/security-journal');
        const resync = page.getByRole('button', { name: /resync|sinkron|integrity|integritas/i }).first();
        if (await resync.isVisible({ timeout: 8000 }).catch(() => false)) {
            await resync.click();
            await expectNoSeriousA11yInDialog(page);
        } else {
            test.skip();
        }
    });
});
