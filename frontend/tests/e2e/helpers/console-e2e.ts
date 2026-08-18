import { expect, type Locator, type Page } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

export const CONSOLE_MAIN = '.console-content-wrap';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'ChangeMeOnFirstLogin!';

export async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/auth/console-sign-in');
    await page.fill('#email', loginEmail);
    await page.fill('#password', loginPassword);
    const submitBtn = page.locator('button[type="submit"]');
    await expect(submitBtn).toBeEnabled();
    await submitBtn.click();
    await expect(page).toHaveURL(/\/dash/, { timeout: 15000 });
}

export function formatA11yViolations(
    violations: Awaited<ReturnType<AxeBuilder['analyze']>>['violations'],
): string {
    if (violations.length === 0) return '';
    return violations
        .map((v) => `${v.impact}: ${v.id} — ${v.help}\n  ${v.nodes.map((n) => n.target.join(', ')).join('\n  ')}`)
        .join('\n\n');
}

export async function expectNoSeriousA11yViolations(
    page: Page,
    options?: { disableRules?: string[]; include?: string },
): Promise<void> {
    let builder = new AxeBuilder({ page });
    if (options?.disableRules?.length) {
        builder = builder.disableRules(options.disableRules);
    }
    const results = await builder
        .include(options?.include ?? CONSOLE_MAIN)
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();

    const serious = results.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
    expect(serious, formatA11yViolations(serious)).toEqual([]);
}

export async function expectNoSeriousA11yInDialog(page: Page): Promise<void> {
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible({ timeout: 10000 });
    const results = await new AxeBuilder({ page })
        .include('[role="dialog"]')
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();
    const serious = results.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
    expect(serious, formatA11yViolations(serious)).toEqual([]);
}

/** English (default) and Indonesian labels for CRM UI. */
export const L_CRM = {
    title: /^CRM$/i,
    settingsTitle: /CRM settings|Pengaturan CRM/i,
    tabs: {
        accounts: /accounts|akun/i,
        contacts: /contacts|kontak/i,
        leads: /leads/i,
        opportunities: /opportunities|peluang/i,
        tickets: /tickets|tiket/i,
        activities: /activities|aktivitas/i,
    },
    section: {
        accounts: /accounts|akun/i,
        contacts: /contacts|kontak/i,
        leads: /leads/i,
        opportunities: /opportunities|peluang/i,
        tickets: /tickets|tiket/i,
    },
    newAccount: /new account|akun baru/i,
    settingsBtn: /settings|pengaturan/i,
    search: /search in this tab|cari di tab ini/i,
};

export async function gotoCrmHub(page: Page): Promise<void> {
    await page.goto('/dash/crm');
    await expect(page.getByRole('heading', { level: 1 })).toHaveText(L_CRM.title, { timeout: 15000 });
}

export async function selectCrmTab(page: Page, tab: keyof typeof L_CRM.tabs): Promise<void> {
    await page.getByRole('tab', { name: L_CRM.tabs[tab] }).click();
}

export const SNAPSHOT_OPTS = {
    maxDiffPixelRatio: 0.03,
    animations: 'disabled' as const,
};

export async function setDarkMode(page: Page, dark: boolean): Promise<void> {
    const themeBtn = page.getByRole('button', { name: /toggle theme|ganti tema|ubah tema/i });
    await expect(themeBtn).toBeVisible();
    const html = page.locator('html');
    const isDark = await html.evaluate((el) => el.classList.contains('dark'));
    if (isDark !== dark) {
        await themeBtn.click();
    }
    if (dark) {
        await expect(html).toHaveClass(/dark/);
    } else {
        await expect(html).not.toHaveClass(/dark/);
    }
}

/** UI refinement: toolbar / filter controls use h-10 (40px) per console design system. */
export async function expectControlHeightPx(
    locator: Locator,
    label: string,
    expectedPx = 40,
    tolerancePx = 4,
): Promise<void> {
    const box = await locator.boundingBox();
    expect(box, `${label} should be visible for height check`).not.toBeNull();
    if (!box) return;
    expect(
        Math.abs(box.height - expectedPx),
        `${label} height ${box.height}px should be ~${expectedPx}px`,
    ).toBeLessThanOrEqual(tolerancePx);
}

/** Page shell per docs/plan/ui-design-system-refinement.md §5 */
export async function expectConsoleListCardShell(page: Page): Promise<void> {
    const glassCard = page.locator('[data-console-glass]').first();
    await expect(glassCard).toBeVisible({ timeout: 15000 });
    await expect(glassCard).toHaveClass(/rounded-xl/);
    await expect(glassCard).toHaveClass(/shadow-none/);
}

export function crmStatGrid(page: Page) {
    return page.locator('.grid').filter({ has: page.getByText(/pipeline value|nilai pipeline/i) }).first();
}

export async function expectActiveTab(page: Page, tabName: RegExp): Promise<void> {
    const tab = page.getByRole('tab', { name: tabName });
    await expect(tab).toHaveAttribute('data-state', 'active');
}

