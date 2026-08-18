import { test, expect, type Page } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'ChangeMeOnFirstLogin!';

/** Stat labels and anchors: English (default) and Indonesian (super admin locale). */
const L = {
    totalMenus: /Total Menus|Total Menu/i,
    activeLocations: /Active Locations|Lokasi Aktif/i,
    totalTags: /Total Tags|Total Tag/i,
    totalWidgets: /Total widgets/i,
    totalRedirects: /Total Redirects|Total Pengalihan/i,
    totalUsers: /Total Users|Total Pengguna/i,
    searchWidgets: /search widgets/i,
    searchRedirects: /search redirects|cari pengalihan/i,
    searchUser: /search user|cari pengguna/i,
    searchTags: /search tags|cari tag/i,
    newWidget: /new widget|widget baru/i,
    newRedirect: /new redirect|pengalihan baru/i,
    newMenu: /new menu|menu baru/i,
    usageColumn: /usage|penggunaan/i,
    activeWidgetStat: /^Active$|^Aktif$/i,
    menusHeading: /Menus|Menu/i,
    widgetsHeading: /Widgets|Widget/i,
    redirectsHeading: /Redirects|Pengalihan/i,
    usersHeading: /Users|Pengguna/i,
    contentStudioHeading: /Content Studio|Studio Konten/i,
    commentsHeading: /All Comments|Semua Komentar/i,
};

async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/auth/console-sign-in');
    await page.fill('#email', loginEmail);
    await page.fill('#password', loginPassword);
    const submitBtn = page.locator('button[type="submit"]');
    await expect(submitBtn).toBeEnabled();
    await submitBtn.click();
    await expect(page).toHaveURL(/\/dash/, { timeout: 15000 });
}

const CONSOLE_MAIN = '.console-content-wrap';

async function expectNoSeriousA11yViolations(page: Page): Promise<void> {
    const results = await new AxeBuilder({ page })
        .include(CONSOLE_MAIN)
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();

    const serious = results.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
    expect(serious, formatA11yViolations(serious)).toEqual([]);
}

function formatA11yViolations(violations: Awaited<ReturnType<AxeBuilder['analyze']>>['violations']): string {
    if (violations.length === 0) return '';
    return violations
        .map((v) => `${v.impact}: ${v.id} — ${v.help}\n  ${v.nodes.map((n) => n.target.join(', ')).join('\n  ')}`)
        .join('\n\n');
}

async function setDarkMode(page: Page, dark: boolean): Promise<void> {
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

/** Menus Index is KeepAlive-cached; ensure list view so stat cards mount. */
async function gotoMenusList(page: Page): Promise<void> {
    await page.goto('/dash/menus');
    await expect(page.getByRole('heading', { level: 1 })).toHaveText(L.menusHeading, { timeout: 15000 });
    const listView = page.getByRole('button', { name: /list view|tampilan daftar/i });
    await listView.click();
    await expect(page.getByText(L.totalMenus)).toBeVisible({ timeout: 15000 });
}

test.describe('Console UI — Content Studio & shell', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('content studio tags tab loads in light and dark mode', async ({ page }) => {
        await page.goto('/dash/contents');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText(L.contentStudioHeading);

        await page.getByRole('tab', { name: /tags|tag/i }).click();
        await expect(page.getByPlaceholder(L.searchTags)).toBeVisible({ timeout: 15000 });

        await setDarkMode(page, true);
        await setDarkMode(page, false);
    });

    test('menus list shows shell stat cards', async ({ page }) => {
        await gotoMenusList(page);

        await expect(page.getByText(L.activeLocations)).toBeVisible();
        await expect(page.getByRole('button', { name: L.newMenu })).toBeVisible();
    });

    test('tags tab in content studio shows usage column', async ({ page }) => {
        await page.goto('/dash/contents');
        await page.getByRole('tab', { name: /tags|tag/i }).click();

        await expect(page.getByPlaceholder(L.searchTags)).toBeVisible({ timeout: 15000 });
        await expect(page.getByRole('columnheader', { name: L.usageColumn })).toBeVisible();

        await expect(page).toHaveScreenshot('content-studio-tags-embedded-light.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });
    });

    test('tags standalone page light and dark snapshots', async ({ page }) => {
        await page.goto('/dash/tags');

        await expect(page.getByText(L.totalTags)).toBeVisible({ timeout: 15000 });
        await expect(page.getByRole('columnheader', { name: L.usageColumn })).toBeVisible();

        await expect(page).toHaveScreenshot('tags-standalone-light.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });

        await setDarkMode(page, true);
        await expect(page).toHaveScreenshot('tags-standalone-dark.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });
    });

    test('comments list shell stat cards light and dark', async ({ page }) => {
        await page.goto('/dash/comments');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText(L.commentsHeading, { timeout: 15000 });
        await expect(page.locator('.space-y-6 > .grid').first().locator(':scope > *')).toHaveCount(5);
        await expect(page.getByPlaceholder(/search comments|cari komentar/i)).toBeVisible();

        await expect(page).toHaveScreenshot('comments-light.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });

        await setDarkMode(page, true);
        await expect(page).toHaveScreenshot('comments-dark.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });
    });

    test('widgets list shell stat cards light and dark snapshots', async ({ page }) => {
        await page.goto('/dash/widgets');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText(L.widgetsHeading, { timeout: 15000 });
        await expect(page.getByText(L.totalWidgets)).toBeVisible();
        await expect(page.getByPlaceholder(L.searchWidgets)).toBeVisible();
        await expect(page.getByRole('button', { name: L.newWidget })).toBeVisible();

        await expect(page).toHaveScreenshot('widgets-light.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });

        await setDarkMode(page, true);
        await expect(page).toHaveScreenshot('widgets-dark.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });
    });

    test('redirects list shell stat cards light and dark snapshots', async ({ page }) => {
        await page.goto('/dash/redirects');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText(L.redirectsHeading, { timeout: 15000 });
        await expect(page.getByText(L.totalRedirects)).toBeVisible({ timeout: 15000 });
        await expect(page.getByPlaceholder(L.searchRedirects)).toBeVisible();
        await expect(page.getByRole('button', { name: L.newRedirect })).toBeVisible();

        await expect(page).toHaveScreenshot('redirects-light.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });

        await setDarkMode(page, true);
        await expect(page).toHaveScreenshot('redirects-dark.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });
    });

    test('widgets toolbar is keyboard reachable', async ({ page }) => {
        await page.goto('/dash/widgets');

        const search = page.getByPlaceholder(L.searchWidgets);
        await expect(search).toBeVisible({ timeout: 15000 });
        await search.focus();
        await expect(search).toBeFocused();

        const newBtn = page.getByRole('button', { name: L.newWidget });
        await newBtn.focus();
        await expect(newBtn).toBeFocused();
    });

    test('widgets active stat filter activates via keyboard', async ({ page }) => {
        await page.goto('/dash/widgets');

        const activeStat = page.getByRole('button', { name: L.activeWidgetStat });
        await expect(activeStat).toBeVisible({ timeout: 15000 });
        await activeStat.focus();
        await page.keyboard.press('Enter');
        await expect(activeStat).toHaveAttribute('aria-pressed', 'true');
    });


    test('menus list light and dark snapshots', async ({ page }) => {
        await gotoMenusList(page);
        await expect(page.getByRole('button', { name: L.newMenu })).toBeVisible();

        await expect(page).toHaveScreenshot('menus-light.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });

        await setDarkMode(page, true);
        await expect(page).toHaveScreenshot('menus-dark.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });
    });

    test('users list shell light and dark snapshots', async ({ page }) => {
        await page.goto('/dash/users');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText(L.usersHeading, { timeout: 15000 });
        await expect(page.getByText(L.totalUsers)).toBeVisible();
        await expect(page.getByPlaceholder(L.searchUser)).toBeVisible();

        await expect(page).toHaveScreenshot('users-light.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });

        await setDarkMode(page, true);
        await expect(page).toHaveScreenshot('users-dark.png', {
            maxDiffPixelRatio: 0.03,
            animations: 'disabled',
        });
    });

    const a11yPages: { path: string; anchor: RegExp; anchorRole?: 'text' | 'placeholder' }[] = [
        { path: '/dash/widgets', anchor: L.searchWidgets, anchorRole: 'placeholder' },
        { path: '/dash/redirects', anchor: L.searchRedirects, anchorRole: 'placeholder' },
        { path: '/dash/menus', anchor: L.totalMenus, anchorRole: 'text' },
        { path: '/dash/users', anchor: L.totalUsers, anchorRole: 'text' },
        { path: '/dash/tags', anchor: L.totalTags, anchorRole: 'text' },
    ];

    for (const { path, anchor, anchorRole } of a11yPages) {
        test(`a11y: ${path} has no serious WCAG violations (light)`, async ({ page }) => {
            await page.goto(path);
            if (path === '/dash/menus') {
                await page.getByRole('button', { name: /list view|tampilan daftar/i }).click();
            }
            await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
            const locator =
                anchorRole === 'placeholder'
                    ? page.getByPlaceholder(anchor)
                    : page.getByText(anchor);
            await expect(locator).toBeVisible({ timeout: 15000 });
            await expectNoSeriousA11yViolations(page);
        });
    }

});
