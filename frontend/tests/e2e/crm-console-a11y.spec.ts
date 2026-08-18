import { test, expect } from '@playwright/test';
import {
    loginAsAdmin,
    gotoCrmHub,
    selectCrmTab,
    expectNoSeriousA11yViolations,
    expectNoSeriousA11yInDialog,
    L_CRM,
    CONSOLE_MAIN,
} from './helpers/console-e2e';

test.describe('CRM console — WCAG axe gate', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('CRM hub — accounts tab', async ({ page }) => {
        await gotoCrmHub(page);
        await expect(page.locator(CONSOLE_MAIN)).toBeVisible();
        await expectNoSeriousA11yViolations(page);
    });

    test('CRM hub — contacts tab', async ({ page }) => {
        await gotoCrmHub(page);
        await selectCrmTab(page, 'contacts');
        await expect(page.getByRole('heading', { level: 2, name: L_CRM.section.contacts })).toBeVisible({
            timeout: 15000,
        });
        await expectNoSeriousA11yViolations(page);
    });

    test('CRM hub — tickets tab', async ({ page }) => {
        await gotoCrmHub(page);
        await selectCrmTab(page, 'tickets');
        await expect(page.getByRole('heading', { level: 2, name: L_CRM.section.tickets })).toBeVisible({
            timeout: 15000,
        });
        await expectNoSeriousA11yViolations(page);
    });

    test('CRM hub — activities tab', async ({ page }) => {
        await gotoCrmHub(page);
        await selectCrmTab(page, 'activities');
        await expect(page.getByText(/activities|aktivitas/i).first()).toBeVisible({ timeout: 15000 });
        await expectNoSeriousA11yViolations(page);
    });

    test('CRM settings page', async ({ page }) => {
        await page.goto('/dash/crm/settings');
        await expect(page.getByRole('heading', { level: 1 })).toHaveText(L_CRM.settingsTitle, {
            timeout: 15000,
        });
        await expectNoSeriousA11yViolations(page);
    });

    test('account create modal', async ({ page }) => {
        await gotoCrmHub(page);
        await page.getByRole('button', { name: L_CRM.newAccount }).click();
        await expectNoSeriousA11yInDialog(page);
        await page.keyboard.press('Escape');
    });

    test('contact create modal', async ({ page }) => {
        await gotoCrmHub(page);
        await selectCrmTab(page, 'contacts');
        await page.getByRole('button', { name: /new contact|kontak baru/i }).click();
        await expectNoSeriousA11yInDialog(page);
        await page.keyboard.press('Escape');
    });

    test('lead create modal', async ({ page }) => {
        await gotoCrmHub(page);
        await selectCrmTab(page, 'leads');
        await page.getByRole('button', { name: /new lead|lead baru/i }).click();
        await expectNoSeriousA11yInDialog(page);
        await page.keyboard.press('Escape');
    });

    test('ticket create modal', async ({ page }) => {
        await gotoCrmHub(page);
        await selectCrmTab(page, 'tickets');
        await page.getByRole('button', { name: /new ticket|tiket baru/i }).click();
        await expectNoSeriousA11yInDialog(page);
        await page.keyboard.press('Escape');
    });
});
