import { test, expect } from '@playwright/test';
import {
    loginAsAdmin,
    gotoCrmHub,
    selectCrmTab,
    L_CRM,
} from './helpers/console-e2e';

test.describe('CRM console — smoke', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('hub loads with CRM heading and default accounts tab', async ({ page }) => {
        await gotoCrmHub(page);
        await expect(page.getByRole('heading', { level: 2, name: L_CRM.section.accounts })).toBeVisible({
            timeout: 15000,
        });
        await expect(page.getByPlaceholder(L_CRM.search)).toBeVisible();
    });

    test('legacy ?tab=settings redirects to CRM settings route', async ({ page }) => {
        await page.goto('/dash/crm?tab=settings');
        await expect(page).toHaveURL(/\/dash\/crm\/settings/, { timeout: 15000 });
        await expect(page.getByRole('heading', { level: 1 })).toHaveText(L_CRM.settingsTitle);
    });

    test('settings page opens from hub action', async ({ page }) => {
        await gotoCrmHub(page);
        await page.getByTestId('crm-settings-link').click();
        await expect(page).toHaveURL(/\/dash\/crm\/settings/);
        await expect(page.getByRole('heading', { level: 1 })).toHaveText(L_CRM.settingsTitle);
    });

    test('each entity tab shows its section heading', async ({ page }) => {
        await gotoCrmHub(page);

        const tabs: Array<{ id: keyof typeof L_CRM.tabs; heading: RegExp }> = [
            { id: 'accounts', heading: L_CRM.section.accounts },
            { id: 'contacts', heading: L_CRM.section.contacts },
            { id: 'leads', heading: L_CRM.section.leads },
            { id: 'opportunities', heading: L_CRM.section.opportunities },
            { id: 'tickets', heading: L_CRM.section.tickets },
        ];

        for (const { id, heading } of tabs) {
            await selectCrmTab(page, id);
            await expect(page.getByRole('heading', { level: 2, name: heading })).toBeVisible({
                timeout: 15000,
            });
        }

        await selectCrmTab(page, 'activities');
        await expect(page.getByText(/activities|aktivitas/i).first()).toBeVisible({ timeout: 15000 });
    });

    test('accounts list pagination appears when CRM has account records', async ({ page }) => {
        await gotoCrmHub(page);

        const hasAccounts = await page.evaluate(async () => {
            const r = await fetch('/api/v1/crm/accounts?per_page=1', { credentials: 'include' });
            if (!r.ok) return false;
            const j = (await r.json()) as { success?: boolean; data?: { total?: number; meta?: { total?: number } } };
            const payload = j.success === true && j.data != null ? j.data : j;
            const total =
                (payload as { total?: number }).total ??
                (payload as { meta?: { total?: number } }).meta?.total ??
                0;
            return total > 0;
        });

        if (!hasAccounts) {
            test.skip(true, 'no CRM accounts in DB — pagination UI not shown');
            return;
        }

        const pagination = page.locator('[data-slot="console-pagination"]');
        await expect(pagination).toBeVisible({ timeout: 15000 });
        await expect(pagination).toContainText(/\d+[\u2013-]\d+\s*\/\s*\d+/);
    });

    test('search input triggers CRM accounts API', async ({ page }) => {
        await gotoCrmHub(page);

        const responsePromise = page.waitForResponse(
            (res) => res.url().includes('/api/v1/crm/accounts') && res.request().method() === 'GET',
            { timeout: 15000 },
        );

        await page.getByPlaceholder(L_CRM.search).fill('e2e');
        await responsePromise;
    });

    test('account create modal opens and closes', async ({ page }) => {
        await gotoCrmHub(page);
        await page.getByRole('button', { name: L_CRM.newAccount }).click();
        await expect(page.getByRole('dialog')).toBeVisible({ timeout: 10000 });
        await page.keyboard.press('Escape');
        await expect(page.getByRole('dialog')).toBeHidden({ timeout: 5000 });
    });
});

test.describe('CRM console — pagination mobile layout', () => {
    test.beforeEach(async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await loginAsAdmin(page);
    });

    test('accounts pagination fits viewport when data exists', async ({ page }) => {
        await gotoCrmHub(page);

        const hasAccounts = await page.evaluate(async () => {
            const r = await fetch('/api/v1/crm/accounts?per_page=1', { credentials: 'include' });
            if (!r.ok) return false;
            const j = (await r.json()) as { success?: boolean; data?: { total?: number } };
            const payload = j.success === true && j.data != null ? j.data : j;
            return ((payload as { total?: number }).total ?? 0) > 0;
        });

        if (!hasAccounts) {
            test.skip(true, 'no CRM accounts in DB');
            return;
        }

        const pagination = page.locator('[data-slot="console-pagination"]');
        await expect(pagination).toBeVisible({ timeout: 15000 });

        const fits = await page.evaluate(() => {
            const wrap = document.querySelector('.console-content-wrap');
            const bar = document.querySelector('[data-slot="console-pagination"]');
            if (!wrap || !bar) return false;
            const w = wrap.getBoundingClientRect();
            const b = bar.getBoundingClientRect();
            return b.left >= w.left - 1 && b.right <= w.right + 1;
        });
        expect(fits).toBe(true);
    });
});
