import { describe, expect, it } from 'vitest';
import { isGenericEngineFavicon, resolveFavicon } from '@/modules/Core/System/utils/favicon';

describe('resolveFavicon', () => {
    it('skips the engine fallback when identity favicon exists', () => {
        expect(resolveFavicon([
            '/favicon.ico',
            '/storage/portal-icon.png',
        ])).toBe('/storage/portal-icon.png');
    });

    it('prefers theme brand favicon over identity and fallback', () => {
        expect(resolveFavicon([
            { url: '/storage/theme-fav.png' },
            '/favicon.ico',
        ])).toBe('/storage/theme-fav.png');
    });

    it('falls back to /favicon.ico when nothing else is set', () => {
        expect(resolveFavicon(['', null, '/favicon.ico'])).toBe('/favicon.ico');
        expect(resolveFavicon([])).toBe('/favicon.ico');
    });

    it('detects generic engine icons', () => {
        expect(isGenericEngineFavicon('/favicon.ico')).toBe(true);
        expect(isGenericEngineFavicon('https://staging.portal.net/favicon.ico')).toBe(true);
        expect(isGenericEngineFavicon('/storage/portal.png')).toBe(false);
    });
});
