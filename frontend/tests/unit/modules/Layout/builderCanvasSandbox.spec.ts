import { describe, expect, it, vi } from 'vitest';

describe('Site Editor Preview Sandboxing', () => {
    describe('Link Interception & Guarding', () => {
        it('prevents navigation when event.defaultPrevented is true', () => {
            // Replicate Vue Router 4's guardEvent logic
            function guardEvent(e: {
                metaKey?: boolean;
                altKey?: boolean;
                ctrlKey?: boolean;
                shiftKey?: boolean;
                defaultPrevented?: boolean;
                button?: number;
            }) {
                if (e.metaKey || e.altKey || e.ctrlKey || e.shiftKey) return false;
                if (e.defaultPrevented) return false;
                if (e.button !== undefined && e.button !== 0) return false;
                return true;
            }

            const unpreventedClick = { defaultPrevented: false, button: 0 };
            expect(guardEvent(unpreventedClick)).toBe(true);

            // With our capture shield, e.defaultPrevented is set to true
            const preventedClick = { defaultPrevented: true, button: 0 };
            expect(guardEvent(preventedClick)).toBe(false);
        });

        it('identifies portal/admin paths as blocked inside preview', () => {
            const isPortalOrAdmin = (path: string) => {
                const clean = path.split('?')[0]?.split('#')[0] ?? '';
                return (
                    clean.startsWith('/manage') ||
                    clean.startsWith('/admin') ||
                    clean.startsWith('/auth') ||
                    clean.startsWith('/member')
                );
            };

            expect(isPortalOrAdmin('/manage/dashboard')).toBe(true);
            expect(isPortalOrAdmin('/auth/login')).toBe(true);
            expect(isPortalOrAdmin('/member/profile')).toBe(true);
            expect(isPortalOrAdmin('/admin/settings')).toBe(true);

            expect(isPortalOrAdmin('/')).toBe(false);
            expect(isPortalOrAdmin('/profil')).toBe(false);
            expect(isPortalOrAdmin('/ppdb')).toBe(false);
            expect(isPortalOrAdmin('/blog/warta-sekolah')).toBe(false);
        });

        it('normalizes internal paths and extracts slug cleanly', () => {
            const extractSlug = (raw: string) => {
                let pathname = raw;
                if (pathname.startsWith('http://') || pathname.startsWith('https://')) {
                    try {
                        const parsed = new URL(raw, 'http://localhost:3000');
                        pathname = parsed.pathname;
                    } catch {
                        return 'home';
                    }
                }
                pathname = (pathname.split('?')[0] ?? '').split('#')[0] ?? '';
                const slug = pathname.replace(/^\/+/, '').replace(/\/+$/, '');
                return slug || 'home';
            };

            expect(extractSlug('/')).toBe('home');
            expect(extractSlug('')).toBe('home');
            expect(extractSlug('/profil')).toBe('profil');
            expect(extractSlug('/profil/')).toBe('profil');
            expect(extractSlug('/ppdb?ref=banner#syarat')).toBe('ppdb');
            expect(extractSlug('http://localhost:3000/kontak')).toBe('kontak');
        });

        it('resolves Indonesian slug aliases correctly to canonical targets', () => {
            const SLUG_ALIASES: Record<string, string> = {
                profil: 'about',
                tentang: 'about',
                'tentang-kami': 'about',
                beranda: 'home',
                kontak: 'contact',
                'hubungi-kami': 'contact',
                'program-keahlian': 'programs',
                jurusan: 'programs',
                fasilitas: 'facilities',
                bengkel: 'facilities',
                prestasi: 'achievement',
                karir: 'career',
                bkk: 'career',
                warta: 'blog',
                'warta-sekolah': 'blog',
                berita: 'blog',
                guru: 'tim',
                ppdb: 'contact',
            };

            const resolveSlug = (slug: string) => SLUG_ALIASES[slug] || slug;

            expect(resolveSlug('profil')).toBe('about');
            expect(resolveSlug('tentang')).toBe('about');
            expect(resolveSlug('beranda')).toBe('home');
            expect(resolveSlug('kontak')).toBe('contact');
            expect(resolveSlug('program-keahlian')).toBe('programs');
            expect(resolveSlug('jurusan')).toBe('programs');
            expect(resolveSlug('fasilitas')).toBe('facilities');
            expect(resolveSlug('prestasi')).toBe('achievement');
            expect(resolveSlug('karir')).toBe('career');
            expect(resolveSlug('bkk')).toBe('career');
            expect(resolveSlug('warta')).toBe('blog');
            expect(resolveSlug('unknown-page')).toBe('unknown-page');
        });
    });

    describe('Theme Page Catalog Fallback', () => {
        it('falls back to static theme catalog when router has no themePage routes', async () => {
            const { getPublicThemePageCatalog } = await import(
                '@/modules/Layout/utils/themePageCatalog'
            );

            // Mock console router with 0 themePage routes
            const mockConsoleRouter = {
                getRoutes: vi.fn(() => [
                    { path: '/console/dashboard', meta: {} },
                    { path: '/console/builder', meta: {} },
                ]),
            } as any;

            const catalog = getPublicThemePageCatalog(mockConsoleRouter);
            expect(Array.isArray(catalog)).toBe(true);
            expect(catalog.length).toBeGreaterThan(0);

            const slugs = catalog.map((item) => item.slug);
            expect(slugs).toContain('home');
            expect(slugs).toContain('about');
            expect(slugs).toContain('programs');
            expect(slugs).toContain('facilities');
            expect(slugs).toContain('contact');
        });
    });
});
