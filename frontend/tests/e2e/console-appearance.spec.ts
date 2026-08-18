import { test, expect, type Page } from '@playwright/test';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'ChangeMeOnFirstLogin!';

const APPEARANCE_PATH = '/dash/settings/console-appearance';
const CONSOLE_MAIN = '.console-content-wrap';

async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/auth/console-sign-in');
    await page.fill('#email', loginEmail);
    await page.fill('#password', loginPassword);
    const submitBtn = page.locator('button[type="submit"]');
    await expect(submitBtn).toBeEnabled();
    await submitBtn.click();
    await expect(page).toHaveURL(/\/dash/, { timeout: 15000 });
}

async function gotoAppearance(page: Page): Promise<void> {
    await page.goto(APPEARANCE_PATH);
    await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
    await expect(page.getByRole('heading', { level: 1 })).toHaveText(
        /Console appearance|Tampilan konsol/i,
    );
    await expect(themeModeGroup(page)).toBeVisible({ timeout: 15000 });
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

function themeModeGroup(page: Page) {
    return page.getByRole('radiogroup', { name: /Theme application|Mode penerapan tema/i });
}

function themeModeRadio(page: Page, mode: 'global' | 'advanced') {
    const group = themeModeGroup(page);
    if (mode === 'global') {
        return group.getByRole('radio', { name: /Global.*Preset|^Global$/i });
    }
    return group.getByRole('radio', { name: /Advanced.*Fine-tune|Advanced|Lanjutan/i });
}

function colorPresetHeading(page: Page) {
    return page.getByRole('heading', { name: /^Color preset$|^Preset warna$/i });
}

function shellChromeHeading(page: Page) {
    return page.getByRole('heading', { name: /Sidebar background style|Gaya latar sidebar/i });
}

async function setThemeMode(page: Page, mode: 'global' | 'advanced'): Promise<void> {
    const radio = themeModeRadio(page, mode);
    const checked = await radio.getAttribute('aria-checked');
    if (checked !== 'true') {
        await radio.click();
    }
    await expect(page.locator('html')).toHaveAttribute('data-console-theme-mode', mode, {
        timeout: 10000,
    });
}

async function openShellChromeTab(page: Page): Promise<void> {
    await setThemeMode(page, 'advanced');
    await page.getByRole('tab', { name: /UI Shell|Desain UI Shell/i }).click();
    await expect(shellChromeHeading(page)).toBeVisible();
}

test.describe('Console appearance settings', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
        await gotoAppearance(page);
    });

    test('loads theme mode switch and main tabs', async ({ page }) => {
        await expect(themeModeRadio(page, 'global')).toBeVisible();
        await expect(themeModeRadio(page, 'advanced')).toBeVisible();

        await expect(page.getByRole('tab', { name: /Colors.*Preset|Warna.*Preset/i })).toBeVisible();
        await expect(page.getByRole('tab', { name: /UI Shell|Desain UI Shell/i })).toBeVisible();
        await expect(page.getByRole('tab', { name: /Logo Assets|Unggah Logo|Logo/i })).toBeVisible();
    });


    test('logos tab lists brand assets; sidebar brand slot is present', async ({ page }) => {
        const logosTab = page.getByRole('tab', { name: /Logo Assets|Unggah Logo|Aset logo/i });
        await expect(logosTab).toBeVisible({ timeout: 15000 });
        await logosTab.click();
        await expect(page.getByText(/Favicon|Ikona favicon/i)).toBeVisible({ timeout: 15000 });
        await expect(
            page.getByText(/Logo \(Light Mode\)|Logo Terang \(Light Mode\)/i).first(),
        ).toBeVisible();

        await page.goto('/dash/menus');
        await page.getByRole('button', { name: /list view|tampilan daftar/i }).click();
        await expect(page.locator('[data-testid="console-sidebar-brand"]')).toBeVisible({
            timeout: 15000,
        });
    });

    test('global mode: colors editable, shell tab gated', async ({ page }) => {
        await setThemeMode(page, 'global');

        await page.getByRole('tab', { name: /Colors.*Preset|Warna.*Preset/i }).click();
        await expect(colorPresetHeading(page)).toBeVisible();

        await page.getByRole('tab', { name: /UI Shell|Desain UI Shell/i }).click();
        const overlay = page.getByRole('status').filter({
            hasText: /Advanced mode required|Perlu mode lanjutan/i,
        });
        await expect(overlay).toBeVisible();
        await expect(
            page.getByRole('button', { name: /Open UI Shell Styles|Buka Desain UI Shell/i }),
        ).toBeVisible();
    });

    test('advanced mode: shell editable, colors tab gated', async ({ page }) => {
        await setThemeMode(page, 'advanced');

        await page.getByRole('tab', { name: /UI Shell|Desain UI Shell/i }).click();
        await expect(shellChromeHeading(page)).toBeVisible({ timeout: 10000 });

        await page.getByRole('tab', { name: /Colors.*Preset|Warna.*Preset/i }).click();
        const overlay = page.getByRole('status').filter({
            hasText: /Global mode required|Perlu mode global/i,
        });
        await expect(overlay).toBeVisible();
        await expect(
            page.getByRole('button', { name: /Open Colors & Presets|Buka Warna & Preset/i }),
        ).toBeVisible();
    });

    test('overlay shortcut switches to advanced and opens shell tab', async ({ page }) => {
        await setThemeMode(page, 'global');

        await page.getByRole('tab', { name: /UI Shell|Desain UI Shell/i }).click();
        await page.getByRole('button', { name: /Open UI Shell Styles|Buka Desain UI Shell/i }).click();

        await expect(page.locator('html')).toHaveAttribute('data-console-theme-mode', 'advanced');
        await expect(page.getByRole('tab', { name: /UI Shell|Desain UI Shell/i })).toHaveAttribute(
            'data-state',
            'active',
        );
        await expect(shellChromeHeading(page)).toBeVisible();
    });

    test('colors tab snapshot in global mode (light)', async ({ page }) => {
        await setThemeMode(page, 'global');
        await page.getByRole('tab', { name: /Colors.*Preset|Warna.*Preset/i }).click();
        await expect(colorPresetHeading(page)).toBeVisible();

        await expect(page.locator(CONSOLE_MAIN)).toHaveScreenshot('console-appearance-colors-global-light.png', {
            maxDiffPixelRatio: 0.04,
            animations: 'disabled',
        });
    });

    test('shell tab snapshot in advanced mode (light)', async ({ page }) => {
        await openShellChromeTab(page);

        await expect(page.locator(CONSOLE_MAIN)).toHaveScreenshot('console-appearance-shell-advanced-light.png', {
            maxDiffPixelRatio: 0.04,
            animations: 'disabled',
        });
    });

    test('colors tab snapshot in global mode (dark)', async ({ page }) => {
        await setThemeMode(page, 'global');
        await page.getByRole('tab', { name: /Colors.*Preset|Warna.*Preset/i }).click();
        await expect(colorPresetHeading(page)).toBeVisible();
        await setDarkMode(page, true);

        await expect(page.locator(CONSOLE_MAIN)).toHaveScreenshot('console-appearance-colors-global-dark.png', {
            maxDiffPixelRatio: 0.04,
            animations: 'disabled',
        });
    });

    test('shell tab snapshot in advanced mode (dark)', async ({ page }) => {
        await openShellChromeTab(page);
        await setDarkMode(page, true);

        await expect(page.locator(CONSOLE_MAIN)).toHaveScreenshot('console-appearance-shell-advanced-dark.png', {
            maxDiffPixelRatio: 0.04,
            animations: 'disabled',
        });
    });

    test('save persists advanced sidebar style after reload', async ({ page }) => {
        await openShellChromeTab(page);

        const cleanCard = page.getByRole('button', { name: /Clean minimal|Minimal bersih/i });
        await cleanCard.click();
        await expect(cleanCard).toHaveClass(/border-primary/);

        await page.getByRole('button', { name: /^Save$|^Simpan$/i }).click();
        await expect(page.getByRole('button', { name: /^Save$|^Simpan$/i })).toBeEnabled({ timeout: 15000 });

        await page.reload();
        await expect(shellChromeHeading(page)).toBeVisible({ timeout: 15000 });
        await expect(page.getByRole('button', { name: /Clean minimal|Minimal bersih/i })).toHaveClass(
            /border-primary/,
        );
        await expect(page.locator('html')).toHaveAttribute('data-console-theme-mode', 'advanced');
    });
});
