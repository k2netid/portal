import { describe, expect, it, beforeEach } from 'vitest';
import {
    defaultHomeForShell,
    isSafeConsoleReturnPath,
    isSafePublicReturnPath,
    rememberRouteBeforeNotFound,
    resolveErrorReturnPath,
    consumeErrorReturnPath,
    sanitizeLoginRedirect,
    trackLastSafeRoute,
} from '@/shared/utils/errorReturn';
import { SECURITY_ROUTES } from '@/config/security';

describe('errorReturn', () => {
    beforeEach(() => {
        sessionStorage.clear();
    });

    describe('public shell', () => {
        it('rejects probe and 404 paths', () => {
            expect(isSafePublicReturnPath('/404')).toBe(false);
            expect(isSafePublicReturnPath('/system')).toBe(false);
            expect(isSafePublicReturnPath('/admin')).toBe(false);
            expect(isSafePublicReturnPath('/dash')).toBe(false);
        });

        it('tracks last safe route per tab session', () => {
            trackLastSafeRoute('/about', 'public');
            trackLastSafeRoute('/blog', 'public');
            expect(sessionStorage.getItem('ja_last_safe_route_public')).toBe('/blog');
        });

        it('uses last safe route when blocking navigation without a safe from path', () => {
            trackLastSafeRoute('/contact', 'public');
            rememberRouteBeforeNotFound('/system', 'public');
            expect(resolveErrorReturnPath('public')).toBe('/contact');
        });

        it('defaults to home when no history exists', () => {
            rememberRouteBeforeNotFound('/system', 'public');
            expect(resolveErrorReturnPath('public')).toBe('/');
            expect(defaultHomeForShell('public', false)).toBe('/');
        });
    });

    describe('console shell', () => {
        it('allows console paths for authenticated operators', () => {
            expect(isSafeConsoleReturnPath('/ja-dash/platform', true)).toBe(true);
            expect(isSafeConsoleReturnPath('/system', true)).toBe(false);
        });

        it('limits guests to auth screens', () => {
            expect(isSafeConsoleReturnPath('/ja-dash', false)).toBe(false);
            expect(isSafeConsoleReturnPath(SECURITY_ROUTES.login, false)).toBe(true);
        });

        it('defaults guest back target to login', () => {
            rememberRouteBeforeNotFound('/admin', 'console', false);
            expect(resolveErrorReturnPath('console', false)).toBe(SECURITY_ROUTES.login);
            expect(defaultHomeForShell('console', false)).toBe(SECURITY_ROUTES.login);
        });
    });

    describe('consumeErrorReturnPath', () => {
        it('resolves and consumes the stored return path', () => {
            sessionStorage.setItem('ja_404_return_public', '/blog');
            expect(consumeErrorReturnPath('public')).toBe('/blog');
            expect(sessionStorage.getItem('ja_404_return_public')).toBeNull();
        });
    });

    describe('sanitizeLoginRedirect', () => {
        it('allows safe public paths', () => {
            expect(sanitizeLoginRedirect('/pricing/isp')).toBe('/pricing/isp');
            expect(sanitizeLoginRedirect('/services')).toBe('/services');
        });

        it('rejects protocol relative and non-string candidates', () => {
            expect(sanitizeLoginRedirect('//evil.com')).toBeUndefined();
            expect(sanitizeLoginRedirect(123 as any)).toBeUndefined();
            expect(sanitizeLoginRedirect('')).toBeUndefined();
        });

        it('rejects probe and error screen paths', () => {
            expect(sanitizeLoginRedirect('/404')).toBeUndefined();
            expect(sanitizeLoginRedirect('/admin')).toBeUndefined();
            expect(sanitizeLoginRedirect('/current', '/current')).toBeUndefined();
        });
    });

    describe('session expired builders', () => {
        it('builds session expired query and href', async () => {
            const { buildSessionExpiredHref, buildSessionExpiredQuery } = await import('@/shared/utils/errorReturn');

            const href = buildSessionExpiredHref({
                reason: 'timeout',
                currentPath: '/blog',
            });
            expect(href).toContain('/419?');
            expect(href).toContain('reason=timeout');
            expect(href).toContain('redirect=%2Fblog');

            const query = buildSessionExpiredQuery({
                reason: 'concurrent',
                currentPath: '/pricing',
            });
            expect(query.reason).toBe('concurrent');
            expect(query.redirect).toBe('/pricing');
        });
    });
});
