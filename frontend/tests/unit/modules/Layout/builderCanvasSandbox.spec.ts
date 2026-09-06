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
    });
});
