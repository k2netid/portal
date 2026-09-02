import { describe, expect, it } from 'vitest';
import { headerChromeWrapClass, isHeaderStickySetting } from '@/modules/Layout/layouts/headerChromeWrap';

describe('headerChromeWrapClass', () => {
    it('puts sticky on the layout wrapper so Layung/Sarangenge can stick', () => {
        expect(headerChromeWrapClass({ sticky: true, janariFixed: false })).toContain('sticky');
        expect(headerChromeWrapClass({ sticky: true, janariFixed: false })).toContain('top-0');
    });

    it('keeps a relative wrap for Janari (header is position:fixed)', () => {
        expect(headerChromeWrapClass({ sticky: true, janariFixed: true })).toBe(
            'relative z-50 overflow-visible',
        );
    });

    it('drops sticky when the setting is off', () => {
        expect(headerChromeWrapClass({ sticky: false, janariFixed: false })).not.toContain('sticky');
    });
});

describe('isHeaderStickySetting', () => {
    it('treats checkbox false-y values as off', () => {
        expect(isHeaderStickySetting(false)).toBe(false);
        expect(isHeaderStickySetting(0)).toBe(false);
        expect(isHeaderStickySetting('false')).toBe(false);
        expect(isHeaderStickySetting(true)).toBe(true);
        expect(isHeaderStickySetting(undefined)).toBe(true);
    });
});
