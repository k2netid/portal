import { describe, expect, it } from 'vitest';
import {
    isLegitimateConsoleSlugPath,
    isProtectedDashboardPath,
    isProbePath,
    isReservedPublicContentSlug,
    shouldBlockOnPublicSite,
    shouldGuestReceiveSecurityNotFound,
} from '@/config/security';

describe('security path helpers', () => {
    it('flags scanner probe paths', () => {
        expect(isProbePath('/admin')).toBe(true);
        expect(isProbePath('/dashboard')).toBe(true);
        expect(isProbePath('/%61dmin')).toBe(true);
        expect(isProbePath('/AdMiN')).toBe(true);
        expect(isProbePath('/%2Fadmin')).toBe(true);
        expect(isProbePath('/manage')).toBe(true);
    });

    it('does not treat legitimate console slug as scanner /dashboard probe', () => {
        expect(isProbePath('/dash')).toBe(false);
        expect(isProbePath('/ja-dash')).toBe(false);
        expect(isLegitimateConsoleSlugPath('/ja-dash/system')).toBe(true);
        expect(isProtectedDashboardPath('/dash')).toBe(true);
        expect(isProtectedDashboardPath('/ja-dash/system/platform')).toBe(true);
    });

    it('guest not-found gate combines probes and console prefixes', () => {
        expect(shouldGuestReceiveSecurityNotFound('/admin')).toBe(true);
        expect(shouldGuestReceiveSecurityNotFound('/dash')).toBe(true);
        expect(shouldGuestReceiveSecurityNotFound('/ja-dash')).toBe(true);
        expect(shouldGuestReceiveSecurityNotFound('/login')).toBe(true);
        expect(shouldGuestReceiveSecurityNotFound('/auth/console-sign-in')).toBe(false);
        expect(shouldGuestReceiveSecurityNotFound('/')).toBe(false);
        expect(shouldGuestReceiveSecurityNotFound('/member/login')).toBe(false);
    });

    it('blocks reserved CMS slugs and public site infra paths', () => {
        expect(isReservedPublicContentSlug('system')).toBe(true);
        expect(isReservedPublicContentSlug('admin')).toBe(true);
        expect(isReservedPublicContentSlug('about')).toBe(false);
        expect(shouldBlockOnPublicSite('/system')).toBe(true);
        expect(shouldBlockOnPublicSite('/auth/console-sign-in')).toBe(true);
        expect(shouldBlockOnPublicSite('/member/login')).toBe(false);
        expect(shouldBlockOnPublicSite('/member/register')).toBe(false);
        expect(shouldBlockOnPublicSite('/member/account')).toBe(false);
    });
});
