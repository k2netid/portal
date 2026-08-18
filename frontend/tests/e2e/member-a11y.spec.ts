import { test, expect, type Page } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const subscriptionDomain = process.env.E2E_SUBSCRIPTION_DOMAIN ?? 'demo.jejakawan.com';
const memberPassword = process.env.E2E_MEMBER_PASSWORD ?? 'Password123!';

const MEMBER_MAIN = '.member-content-wrap, .member-auth-enterprise';

function formatA11yViolations(violations: Awaited<ReturnType<AxeBuilder['analyze']>>['violations']): string {
    if (violations.length === 0) return '';
    return violations
        .map((v) => `${v.impact}: ${v.id} — ${v.help}\n  ${v.nodes.map((n) => n.target.join(', ')).join('\n  ')}`)
        .join('\n\n');
}

async function expectNoSeriousA11y(page: Page, selector: string): Promise<void> {
    const results = await new AxeBuilder({ page })
        .include(selector)
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();
    const serious = results.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
    expect(serious, formatA11yViolations(serious)).toEqual([]);
}

test.describe('Member — WCAG axe gate (K4)', () => {
    test('login page', async ({ page }) => {
        await page.goto('/member/login');
        await expect(page.getByRole('heading', { level: 2 })).toBeVisible({ timeout: 15000 });
        await expectNoSeriousA11y(page, '.member-auth-enterprise');
    });

    test('register page', async ({ page }) => {
        await page.goto('/member/register');
        await expect(page.getByRole('heading', { level: 2 })).toBeVisible({ timeout: 15000 });
        await expectNoSeriousA11y(page, '.member-auth-enterprise');
    });

    test('dashboard after registration', async ({ page }) => {
        const unique = Date.now();
        const email = `e2e-a11y-${unique}@academy.test`;
        await page.goto('/member/register');
        await page.fill('#subscription-domain', subscriptionDomain);
        await expect(page.getByRole('button', { name: /register|daftar/i })).toBeEnabled({ timeout: 15000 });
        await page.fill('#name', `E2E A11y ${unique}`);
        await page.fill('#email', email);
        await page.fill('#password', memberPassword);
        await page.fill('#password_confirmation', memberPassword);
        await page.getByRole('button', { name: /register|daftar/i }).click();
        await expect(page).toHaveURL(/\/member\/?$/, { timeout: 15000 });
        await expectNoSeriousA11y(page, MEMBER_MAIN);
    });

    test('member search dialog', async ({ page }) => {
        const unique = Date.now();
        const email = `e2e-search-${unique}@academy.test`;
        await page.goto('/member/register');
        await page.fill('#subscription-domain', subscriptionDomain);
        await expect(page.getByRole('button', { name: /register|daftar/i })).toBeEnabled({ timeout: 15000 });
        await page.fill('#name', `E2E Search ${unique}`);
        await page.fill('#email', email);
        await page.fill('#password', memberPassword);
        await page.fill('#password_confirmation', memberPassword);
        await page.getByRole('button', { name: /register|daftar/i }).click();
        await expect(page).toHaveURL(/\/member\/?$/, { timeout: 15000 });
        await page.keyboard.press('Control+k');
        const dialog = page.getByRole('dialog');
        await expect(dialog).toBeVisible({ timeout: 5000 });
        const results = await new AxeBuilder({ page })
            .include('[role="dialog"]')
            .disableRules(['color-contrast'])
            .withTags(['wcag2a', 'wcag2aa'])
            .analyze();
        const serious = results.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
        expect(serious, formatA11yViolations(serious)).toEqual([]);
    });
});
