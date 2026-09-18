import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers/console-e2e';

test.describe('3-Level Theme Registry Architecture & Quota Flow', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('App Store displays Themes tab, 3-level architecture banner, quota badge, and served theme indicators', async ({ page }) => {
        await page.goto('/dash/settings/extensions');
        await page.waitForLoadState('networkidle');

        // Verify page header
        await expect(page.getByRole('heading', { name: /Ekstensi|Extensions|App Store/i }).first()).toBeVisible({ timeout: 15000 });

        // Switch to the dedicated Themes tab
        const themesTab = page.getByRole('button', { name: /^Themes$|^Tema$|^Téma$/i }).first();
        await expect(themesTab).toBeVisible({ timeout: 10000 });
        await themesTab.click();

        // 1. Verify 3-Level Theme Architecture Educational Banner
        const bannerTitle = page.locator('text=/Arsitektur 3-Tingkat Tema|3-Level Theme Architecture|Arsitéktur 3-Tingkat Téma/i').first();
        await expect(bannerTitle).toBeVisible({ timeout: 10000 });

        // Verify Quota Badge in the banner
        const quotaBadge = page.locator('text=/Tier:|Kuota:|Quota:/i').first();
        await expect(quotaBadge).toBeVisible();

        // Verify 3 Pipeline Level Columns
        const level1Col = page.locator('text=/Tingkat 1: Inventaris Paket|Level 1: Pack Inventory|Tingkat 1: Inventaris Pakét/i').first();
        await expect(level1Col).toBeVisible();

        const level2Col = page.locator('text=/Tingkat 2: Tema Dilayani|Level 2: Served Theme|Tingkat 2: Téma Dilayanan/i').first();
        await expect(level2Col).toBeVisible();

        const level3Col = page.locator('text=/Tingkat 3: Situs Publik|Level 3: Public Website/i').first();
        await expect(level3Col).toBeVisible();

        // Verify navigation button to Themes Manager
        const goToThemesBtn = page.getByRole('button', { name: /Buka Manajer Tema|Go to Themes Manager/i }).first();
        await expect(goToThemesBtn).toBeVisible();

        // 2. Verify Theme Cards in the Grid
        // theme-janari must be visible
        const janariSlug = page.locator('text=theme-janari').first();
        await expect(janariSlug).toBeVisible({ timeout: 10000 });

        // Locate Janari card container
        const janariCard = page.locator('.group', { has: page.locator('text=theme-janari') }).first();
        await expect(janariCard).toBeVisible();

        // Currently served theme indicator
        const servedBadge = janariCard.locator('text=/Tema Dilayani|Currently Served Theme|Téma Dilayanan/i').first();
        await expect(servedBadge).toBeVisible();

        // Customizer shortcut button on served theme
        const customizerBtn = janariCard.getByRole('button', { name: /Buka Customizer|Open Customizer/i }).first();
        await expect(customizerBtn).toBeVisible();

        // Invariant: theme-janari is the baseline theme and must be locked (cannot be deactivated)
        const lockedIndicator = janariCard.locator('text=/Terkunci|Locked/i').first();
        await expect(lockedIndicator).toBeVisible();

        // Other theme packs (e.g. theme-layung or theme-sarangenge) should also be displayed under Themes tab
        const layungSlug = page.locator('text=theme-layung').first();
        await expect(layungSlug).toBeVisible({ timeout: 5000 });

        // Non-theme extensions (e.g. floating-social-dock) should NOT be displayed when filtering by Themes
        const dockSlug = page.locator('text=floating-social-dock');
        await expect(dockSlug).toHaveCount(0);
    });

    test('Themes Manager displays 3-level pipeline header, quota summary, entitlement badges, and graceful action locks', async ({ page }) => {
        await page.goto('/dash/themes');
        await page.waitForLoadState('networkidle');

        // 1. Verify 3-Level Theme Pipeline Header
        const headerTitle = page.locator('text=/Arsitektur 3-Tingkat Tema|3-Level Theme Architecture|Arsitéktur 3-Tingkat Téma/i').first();
        await expect(headerTitle).toBeVisible({ timeout: 15000 });

        // Verify Quota badge & note
        const quotaBadge = page.locator('text=/Lisensi:|License:/i').first();
        await expect(quotaBadge).toBeVisible();

        // Verify 3 Pipeline Step Indicators in Themes Manager
        const level1Step = page.locator('text=/Tingkat 1: Katalog Paket|Level 1: Pack Catalog/i').first();
        await expect(level1Step).toBeVisible();

        const level2Step = page.locator('text=/Tingkat 2: Tema Dilayani|Level 2: Served Theme/i').first();
        await expect(level2Step).toBeVisible();

        const level3Step = page.locator('text=/Tingkat 3: Situs Publik|Level 3: Public Website/i').first();
        await expect(level3Step).toBeVisible();

        // Verify shortcut button to App Store & Extensions
        const appStoreBtn = page.getByRole('button', { name: /App Store & Ekstensi|App Store & Extensions/i }).first();
        await expect(appStoreBtn).toBeVisible();

        // 2. Verify Janari Theme Card & Badges
        const janariHeading = page.getByRole('heading', { name: /Janari/i }).first();
        await expect(janariHeading).toBeVisible({ timeout: 10000 });

        const janariCard = page.locator('.group', { has: janariHeading }).first();
        await expect(janariCard).toBeVisible();

        // Entitlement badge: Free Baseline
        const baselineBadge = janariCard.locator('text=/Basis Gratis|Free Baseline|Dadasar Gratis/i').first();
        await expect(baselineBadge).toBeVisible();

        // Pack Inventory badge: Pack Enabled
        const packBadge = janariCard.locator('text=/Paket Aktif|Pack Enabled|Pakét Aktif/i').first();
        await expect(packBadge).toBeVisible();

        // Active state badge
        const activeBadge = janariCard.locator('text=/Aktif|Active/i').first();
        await expect(activeBadge).toBeVisible();

        // Active theme provides Open Customizer button
        const customizerBtn = janariCard.getByRole('button', { name: /Buka Customizer|Open Customizer/i }).first();
        await expect(customizerBtn).toBeVisible();

        // 3. Verify Premium Themes (Layung / Sarangenge / Sareupna)
        const premiumThemes = page.locator('.group', {
            has: page.locator('text=/Layung|Sarangenge|Sareupna/i'),
        });
        const premiumCount = await premiumThemes.count();
        expect(premiumCount).toBeGreaterThan(0);

        // Verify that premium theme cards have entitlement badge
        const firstPremium = premiumThemes.first();
        const entitlementBadge = firstPremium.locator('text=/Termasuk PRO|PRO Included|Perlu Lisensi PRO|PRO Required|Peryogi Lisensi PRO/i').first();
        await expect(entitlementBadge).toBeVisible();

        // If not entitled, activate button is disabled or has PRO lock label
        const isLocked = await firstPremium.locator('button:has-text("Perlu Lisensi PRO"), button:has-text("PRO Required")').count() > 0;
        const isEntitled = await firstPremium.locator('button:has-text("Aktifkan"), button:has-text("Activate")').count() > 0;
        expect(isLocked || isEntitled).toBe(true);
    });

    test('Inter-page navigation connects App Store and Themes Manager seamlessly', async ({ page }) => {
        // Start at Themes Manager
        await page.goto('/dash/themes');
        await page.waitForLoadState('networkidle');

        // Click shortcut to App Store
        const appStoreBtn = page.getByRole('button', { name: /App Store & Ekstensi|App Store & Extensions/i }).first();
        await expect(appStoreBtn).toBeVisible({ timeout: 15000 });
        await appStoreBtn.click();

        // Verify navigation to Extensions / App Store
        await expect(page).toHaveURL(/\/(?:dash|ja-dash)\/(?:settings\/extensions|extensions)/, { timeout: 15000 });

        // Switch to Themes tab
        const themesTab = page.getByRole('button', { name: /^Themes$|^Tema$|^Téma$/i }).first();
        await expect(themesTab).toBeVisible({ timeout: 10000 });
        await themesTab.click();

        // Click shortcut to Themes Manager
        const themesMgrBtn = page.getByRole('button', { name: /Buka Manajer Tema|Go to Themes Manager/i }).first();
        await expect(themesMgrBtn).toBeVisible({ timeout: 10000 });
        await themesMgrBtn.click();

        // Verify navigation back to Themes Manager
        await expect(page).toHaveURL(/\/(?:dash|ja-dash)\/themes/, { timeout: 15000 });
    });
});
