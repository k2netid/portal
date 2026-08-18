/**
 * CRM console UI/UX — design system refinement gate.
 * @see docs/plan/ui-design-system-refinement.md (§5 Page shell, §7 Fase J snapshots)
 */
import { test, expect } from '@playwright/test';
import {
    loginAsAdmin,
    gotoCrmHub,
    setDarkMode,
    L_CRM,
    CONSOLE_MAIN,
    SNAPSHOT_OPTS,
    expectConsoleListCardShell,
    expectControlHeightPx,
    expectActiveTab,
} from './helpers/console-e2e';

const L_UI = {
    pipelineValue: /pipeline value|nilai pipeline/i,
    openTickets: /open tickets|tiket terbuka/i,
    accountsStat: /accounts|akun/i,
    contactsStat: /contacts|kontak/i,
};

test.describe('CRM console — UI refinement (design system)', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('hub uses PageHeader + ConsoleStatCard grid + ConsoleListCard shell', async ({ page }) => {
        await gotoCrmHub(page);

        await expect(page.getByRole('heading', { level: 1 })).toHaveText(L_CRM.title);
        const statGrid = page.locator('.grid').filter({ has: page.getByText(L_UI.pipelineValue) }).first();
        await expect(statGrid.getByRole('button')).toHaveCount(6, { timeout: 15000 });
        await expect(page.getByText(L_UI.pipelineValue)).toBeVisible({ timeout: 15000 });
        await expect(page.getByText(L_UI.openTickets)).toBeVisible();

        await expectConsoleListCardShell(page);
        await expect(page.getByRole('tablist')).toBeVisible();
    });

    test('toolbar controls follow h-10 sizing (PageHeader actions + search)', async ({ page }) => {
        await gotoCrmHub(page);

        const refreshBtn = page.getByRole('button', { name: /refresh|muat ulang/i });
        await expect(refreshBtn).toBeVisible();
        await expectControlHeightPx(refreshBtn, 'refresh button');

        const search = page.getByPlaceholder(L_CRM.search);
        await expectControlHeightPx(search, 'CRM search input');
    });

    test('list tab toolbar uses sm + h-10 primary CTA pattern', async ({ page }) => {
        await gotoCrmHub(page);
        const newAccount = page.getByRole('button', { name: L_CRM.newAccount });
        await expect(newAccount).toBeVisible();
        await expectControlHeightPx(newAccount, 'new account CTA');
    });

    test('accounts tab uses embedded DataTable inside list card', async ({ page }) => {
        await gotoCrmHub(page);
        await expectActiveTab(page, L_CRM.tabs.accounts);

        const listCard = page.locator('[data-console-glass]').first();
        const table = listCard.locator('table');
        await expect(table).toBeVisible({ timeout: 15000 });
    });

    test('stat card keyboard activates tab switch (contacts)', async ({ page }) => {
        await gotoCrmHub(page);
        await expectActiveTab(page, L_CRM.tabs.accounts);

        const statGrid = page.locator('.grid').filter({ has: page.getByText(L_UI.pipelineValue) }).first();
        const contactsStat = statGrid.getByRole('button', { name: L_UI.contactsStat });
        await expect(contactsStat).toBeVisible({ timeout: 15000 });
        await contactsStat.focus();
        await page.keyboard.press('Enter');

        await expectActiveTab(page, L_CRM.tabs.contacts);
        await expect(page.getByRole('heading', { level: 2, name: L_CRM.section.contacts })).toBeVisible();
    });

    test('CRM hub light and dark visual snapshots', async ({ page }) => {
        await gotoCrmHub(page);
        await expect(page.locator(CONSOLE_MAIN)).toBeVisible();

        await expect(page.locator(CONSOLE_MAIN)).toHaveScreenshot('crm-hub-light.png', SNAPSHOT_OPTS);

        await setDarkMode(page, true);
        await expect(page.locator(CONSOLE_MAIN)).toHaveScreenshot('crm-hub-dark.png', SNAPSHOT_OPTS);
    });

    test('CRM settings page matches list shell pattern', async ({ page }) => {
        await page.goto('/dash/crm/settings');
        await expect(page.getByRole('heading', { level: 1 })).toHaveText(L_CRM.settingsTitle, {
            timeout: 15000,
        });
        await expectConsoleListCardShell(page);

        await expect(page.locator(CONSOLE_MAIN)).toHaveScreenshot('crm-settings-light.png', SNAPSHOT_OPTS);
    });

    test('account form modal uses console-dialog-md width token', async ({ page }) => {
        await gotoCrmHub(page);
        await page.getByRole('button', { name: L_CRM.newAccount }).click();

        const dialog = page.getByRole('dialog');
        await expect(dialog).toBeVisible({ timeout: 10000 });
        await expect(dialog).toHaveClass(/console-dialog-md/);

        const footerCancel = dialog.getByRole('button', { name: /cancel|batal/i });
        const footerSave = dialog.getByRole('button', { name: /save|simpan/i });
        await expect(footerCancel).toBeVisible();
        await expect(footerSave).toBeVisible();
        await expectControlHeightPx(footerSave, 'modal save button');

        await page.keyboard.press('Escape');
    });

    test('filter bar selects use h-10 height on accounts tab', async ({ page }) => {
        await gotoCrmHub(page);
        const statusFilter = page.locator('[data-console-glass]').first().getByRole('combobox').first();
        await expect(statusFilter).toBeVisible({ timeout: 15000 });
        await expectControlHeightPx(statusFilter, 'CRM filter select');
    });
});
