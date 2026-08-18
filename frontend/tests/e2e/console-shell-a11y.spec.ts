import { test, expect, type Page } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const loginEmail = process.env.E2E_LOGIN_EMAIL ?? 'super@jejakawan.com';
const loginPassword = process.env.E2E_LOGIN_PASSWORD ?? 'ChangeMeOnFirstLogin!';

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

function formatA11yViolations(violations: Awaited<ReturnType<AxeBuilder['analyze']>>['violations']): string {
    if (violations.length === 0) return '';
    return violations
        .map((v) => `${v.impact}: ${v.id} — ${v.help}\n  ${v.nodes.map((n) => n.target.join(', ')).join('\n  ')}`)
        .join('\n\n');
}

async function expectNoSeriousA11yViolations(page: Page, options?: { disableRules?: string[] }): Promise<void> {
    let builder = new AxeBuilder({ page })
    if (options?.disableRules?.length) {
        builder = builder.disableRules(options.disableRules);
    }
    const results = await builder
        .include(CONSOLE_MAIN)
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();

    const serious = results.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
    expect(serious, formatA11yViolations(serious)).toEqual([]);
}


async function firstApiId(page: Page, path: string): Promise<string | null> {
    return page.evaluate(async (apiPath) => {
        const listRows = (payload: unknown): Array<Record<string, unknown>> => {
            if (payload == null) return [];
            if (Array.isArray(payload)) {
                return payload.filter((r): r is Record<string, unknown> => r != null && typeof r === 'object');
            }
            if (typeof payload !== 'object') return [];
            const p = payload as Record<string, unknown>;
            if (Array.isArray(p.data)) {
                return p.data.filter((r): r is Record<string, unknown> => r != null && typeof r === 'object');
            }
            if (Array.isArray(p.items)) {
                return p.items.filter((r): r is Record<string, unknown> => r != null && typeof r === 'object');
            }
            return [];
        };
        const r = await fetch(`/api/v1${apiPath}?per_page=5`, { credentials: 'include' });
        if (!r.ok) return null;
        const j = (await r.json()) as Record<string, unknown>;
        const payload = j.success === true && j.data != null ? j.data : j;
        const row = listRows(payload)[0];
        return row?.id != null ? String(row.id) : null;
    }, path);
}

async function firstApiSlug(page: Page, path: string): Promise<string | null> {
    return page.evaluate(async (apiPath) => {
        const listRows = (payload: unknown): Array<Record<string, unknown>> => {
            if (payload == null) return [];
            if (Array.isArray(payload)) {
                return payload.filter((r): r is Record<string, unknown> => r != null && typeof r === 'object');
            }
            if (typeof payload !== 'object') return [];
            const p = payload as Record<string, unknown>;
            if (Array.isArray(p.data)) {
                return p.data.filter((r): r is Record<string, unknown> => r != null && typeof r === 'object');
            }
            if (Array.isArray(p.items)) {
                return p.items.filter((r): r is Record<string, unknown> => r != null && typeof r === 'object');
            }
            return [];
        };
        const r = await fetch(`/api/v1${apiPath}?per_page=5`, { credentials: 'include' });
        if (!r.ok) return null;
        const j = (await r.json()) as Record<string, unknown>;
        const payload = j.success === true && j.data != null ? j.data : j;
        const row = listRows(payload)[0];
        return row?.slug != null ? String(row.slug) : null;
    }, path);
}

type A11yPageCase = {
    path: string;
    ready: (page: Page) => Promise<false | void>;
};

const shellA11yPages: A11yPageCase[] = [
    {
        path: '/dash/crm',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(/^CRM$/i, { timeout: 15000 });
        },
    },
    {
        path: '/dash/crm/settings',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /CRM settings|Pengaturan CRM/i,
                { timeout: 15000 },
            );
        },
    },

    {
        path: '/dash/contents',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Content|Konten/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/comments',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Comment|Komentar/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/categories',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Categor|Kategori/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/content-templates/create',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Template|Templat/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/contents/create',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Create|Buat|New|Baru|Konten|Content/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/themes',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Theme|Tema/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/publishing/settings',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Publishing|Setting|Penerbitan|CMS|Pengaturan/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/media',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Media Library|Pustaka Media/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/custom-fields',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Custom Fields|Bidang Kustom/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/tags',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Tags|Tag|Label/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/seo',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /SEO Tools|Peralatan SEO/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/redirects',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Redirects|Pengalihan/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/ai',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /AI Studio|Studio AI/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/forms',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Form Builder|Pembuat Formulir/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/newsletter',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Newsletter|Pelanggan Buletin|Buletin/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/email-templates',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Email Templates|Template Email/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/roles',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Roles|Peran|Permissions|Izin/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/system-journal',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /System Journal|Jurnal Sistem/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/settings',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Settings|Pengaturan/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/languages',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Languages|Bahasa/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/system',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /System Information|Informasi Sistem/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/scheduled-tasks',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Scheduled Tasks|Tugas Terjadwal/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/plugins',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Plugins|Plugin/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/webhooks',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Webhooks|Webhook/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/cck',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Content types|Tipe konten|CCK/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/file-manager',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /File Manager|Manajer Berkas/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/platform',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Platform console|Konsol platform|Platform/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/security-journal',
        ready: async (page) => {
            await expect(
                page.getByText(/Security Maintenance Mode|Mode Pemeliharaan Keamanan/i),
            ).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/settings/console-appearance',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Console appearance|Tampilan konsol/i,
            );
            await expect(
                page.getByRole('radiogroup', { name: /Theme application|Mode penerapan tema/i }),
            ).toBeVisible();
        },
    },
    {
        path: '/dash/extensions',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toBeVisible({ timeout: 15000 });
            await expect(page.getByPlaceholder(/search|Cari/i)).toBeVisible();
        },
    },

    {
        path: '/dash/menus',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/users',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Users|Pengguna/i, { timeout: 15000 });
        },
    },
    {
        path: '/dash/redis',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Redis/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/analytics',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Analytics|Analitik/i,
                { timeout: 15000 },
            );
            await expect(page.getByRole('button', { name: /Apply|Terapkan/i })).toBeVisible();
        },
    },
    {
        path: '/dash/journal-dashboard',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Journal Dashboard|Dasbor Jurnal/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/activity-journal',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Activity Journal|Jurnal Aktivitas/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/search',
        ready: async (page) => {
            await expect(
                page.getByRole('heading', { name: /Pencarian.*indeks|Search.*index/i }),
            ).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/access-journal',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Access History|Riwayat Akses/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/backups',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(
                /Backup|Cadangan/i,
                { timeout: 15000 },
            );
        },
    },
    {
        path: '/dash/widgets',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Widget|Widget/i, { timeout: 15000 });
        },
    },
    {
        path: '/dash/cache',
        ready: async (page) => {
            await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/forms/create',
        ready: async (page) => {
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Create|Buat|form|Formulir/i, { timeout: 15000 });
        },
    },
    {
        path: '/dash/roles/create',
        ready: async (page) => {
            await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/users/create',
        ready: async (page) => {
            await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/profile',
        ready: async (page) => {
            await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/onboarding',
        ready: async (page) => {
            await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/notifications',
        ready: async (page) => {
            await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/content-templates',
        ready: async (page) => {
            await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/cck/new',
        ready: async (page) => {
            await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '/dash/system/notifications',
        ready: async (page) => {
            await expect(page.getByText(/Notification|Notifikasi/i).first()).toBeVisible({ timeout: 15000 });
        },
    },
    {
        path: '__nav__/dash/forms/:id/edit',
        ready: async (page) => {
            const id = await firstApiId(page, '/manage/forms');
            if (!id) return false;
            await page.goto(`/dash/forms/${id}/edit`);
                    },
    },
    {
        path: '__nav__/dash/forms/:id/submissions',
        ready: async (page) => {
            const id = await firstApiId(page, '/manage/forms');
            if (!id) return false;
            await page.goto(`/dash/forms/${id}/submissions`);
                    },
    },
    {
        path: '__nav__/dash/forms/:id/analytics',
        ready: async (page) => {
            const id = await firstApiId(page, '/manage/forms');
            if (!id) return false;
            await page.goto(`/dash/forms/${id}/analytics`);
                    },
    },
    {
        path: '__nav__/dash/roles/:id/edit',
        ready: async (page) => {
            const id = await firstApiId(page, '/manage/system/roles');
            if (!id) return false;
            await page.goto(`/dash/roles/${id}/edit`);
                    },
    },
    {
        path: '__nav__/dash/users/:id/edit',
        ready: async (page) => {
            const id = await firstApiId(page, '/manage/system/users');
            if (!id) return false;
            await page.goto(`/dash/users/${id}/edit`);
                    },
    },
    {
        path: '__nav__/dash/cck/:id',
        ready: async (page) => {
            const id = await firstApiId(page, '/manage/infra/cck/types');
            if (!id) return false;
            await page.goto(`/dash/cck/${id}`);
                    },
    },
    {
        path: '/dash/email-templates/create',
        ready: async (page) => {
            await page.goto('/dash/email-templates/create');
            await expect(page.getByRole('heading', { level: 1 })).toHaveText(/Create|Buat|template|Template/i, { timeout: 15000 });
                    },
    },
    {
        path: '__nav__/dash/email-templates/:id/edit',
        ready: async (page) => {
            const id = await firstApiId(page, '/manage/system/email-templates');
            if (!id) return false;
            await page.goto(`/dash/email-templates/${id}/edit`);
                    },
    },
    {
        path: '__nav__/dash/content-templates/:id/edit',
        ready: async (page) => {
            const id = await firstApiId(page, '/manage/publishing/content-templates');
            if (!id) return false;
            await page.goto(`/dash/content-templates/${id}/edit`);
                    },
    },
    {
        path: '__nav__/dash/contents/calendar',
        ready: async (page) => {
            await page.goto('/dash/contents/calendar');
                    },
    },
    {
        path: '__nav__/dash/themes/customizer',
        ready: async (page) => {
            const slug = await firstApiSlug(page, '/manage/layout/themes');
            if (!slug) return false;
            await page.goto(`/dash/themes/${slug}/customizer`);
                    },
    },

];

test.describe('Console shell — WCAG axe gate', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    for (const { path, ready } of shellA11yPages) {
        test(`no serious a11y violations on ${path}`, async ({ page }) => {
            if (!path.startsWith('__nav__')) {
                await page.goto(path);
                await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
            }
            const readyResult = await ready(page);
            if (path.startsWith('__nav__') && readyResult === false) {
                test.skip(true, 'no seeded record for dynamic route');
            }
            if (path.startsWith('__nav__')) {
                await expect(page.locator(CONSOLE_MAIN)).toBeVisible({ timeout: 15000 });
            }
            await expectNoSeriousA11yViolations(page, (() => {
                if (path.includes('calendar')) return { disableRules: ['nested-interactive', 'role-img-alt'] };
                if (path.includes('analytics')) return { disableRules: ['role-img-alt'] };
                return undefined;
            })());
        });
    }
});
