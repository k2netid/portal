import { describe, expect, it, beforeEach } from 'vitest';
import {
    defaultHomeForShell,
    isSafeConsoleReturnPath,
    isSafePublicReturnPath,
    rememberRouteBeforeNotFound,
    resolveErrorReturnPath,
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
});
