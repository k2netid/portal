import { expect, test } from '@playwright/test';

test.describe('Sarangenge Dynamic Content Verification', () => {
    test('homepage renders dynamic sections (Announcements, Achievements, Extracurricular, Testimonials)', async ({ page }) => {
        await page.goto('/');

        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('header').first()).toBeVisible({ timeout: 15000 });

        // Verify AnnouncementsSection (Agenda)
        const announcementsSection = page.locator('[data-ja-customizer-target="news"]');
        await expect(announcementsSection).toBeVisible({ timeout: 15000 });
        await expect(announcementsSection.getByText(/Agenda & Kabar/i)).toBeVisible();

        // Verify AchievementsSection
        const achievementsSection = page.locator('[data-ja-customizer-target="achievements"]');
        await expect(achievementsSection).toBeVisible({ timeout: 15000 });
        await expect(achievementsSection.getByText(/Prestasi & Penghargaan/i)).toBeVisible();

        // Verify ExtracurricularSection
        await expect(page.getByText(/Ekstrakurikuler & Pengembangan Diri/i)).toBeVisible({ timeout: 15000 });

        // Verify TestimonialsSection
        const testimonialsSection = page.locator('[data-ja-customizer-target="testimonials"]');
        await expect(testimonialsSection).toBeVisible({ timeout: 15000 });
        await expect(testimonialsSection.getByText(/Apa Kata Siswa, Orang Tua & Alumni/i)).toBeVisible();
    });

    test('/achievement renders dynamic items with tabs and detail links', async ({ page }) => {
        await page.goto('/achievement');

        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('h1').first()).toBeVisible({ timeout: 15000 });
        await expect(page.getByRole('button', { name: /Semua Prestasi/i })).toBeVisible();

        // Verify cards rendered
        const cards = page.locator('a[href*="/blog/"]');
        await expect(cards.first()).toBeVisible({ timeout: 15000 });
    });

    test('/tim renders dynamic staff directory with profile links', async ({ page }) => {
        await page.goto('/tim');

        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('h1').first()).toBeVisible({ timeout: 15000 });

        // Verify staff directory items
        await expect(page.getByText(/Drs. H. Rahmat Sudrajat/i)).toBeVisible({ timeout: 15000 });
        await expect(page.getByText('Kepala Sekolah', { exact: true })).toBeVisible();
    });

    test('/career renders dynamic alumni stories and statistics', async ({ page }) => {
        await page.goto('/career');

        await expect(page.getByText(/halaman tidak stabil/i)).toHaveCount(0, { timeout: 15000 });
        await expect(page.locator('h1').first()).toBeVisible({ timeout: 15000 });

        // Verify alumni stories
        await expect(page.getByText(/Kisah Sukses Alumni/i)).toBeVisible({ timeout: 15000 });
        await expect(page.getByText(/dr. Farhan Maulana/i)).toBeVisible();
    });
});
