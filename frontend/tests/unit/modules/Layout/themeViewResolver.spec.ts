import { describe, expect, it } from 'vitest';
import {
    BUNDLED_FRONTEND_THEME_SLUGS,
    buildThemeViewResolveCandidates,
    findThemeViewKey,
} from '@/modules/Layout/utils/themeViewResolver';

const modules = {
    '/src/modules/Layout/views/themes/Index.vue': () => Promise.resolve({}),
    '/src/modules/Layout/views/themes/ThemeCustomizer.vue': () => Promise.resolve({}),
    '/src/modules/Layout/views/themes/zenith/components/layout/Header.vue': () => Promise.resolve({}),
    '/src/modules/Layout/views/themes/zenith/components/layout/Footer.vue': () => Promise.resolve({}),
    '/src/modules/Layout/views/themes/zenith/pages/Home.vue': () => Promise.resolve({}),
};

describe('themeViewResolver', () => {
    it('falls back to bundled janari/zenith when theme slug is missing', () => {
        expect(buildThemeViewResolveCandidates(null)).toEqual([...BUNDLED_FRONTEND_THEME_SLUGS]);
        expect(BUNDLED_FRONTEND_THEME_SLUGS[0]).toBe('janari');
        expect(buildThemeViewResolveCandidates({ name: 'X', slug: '', type: 'frontend' })).toEqual([
            ...BUNDLED_FRONTEND_THEME_SLUGS,
        ]);
    });

    it('resolves components/Header from zenith when only zenith views exist', () => {
        const key = findThemeViewKey(modules, [], 'components/Header');
        expect(key).toContain('zenith/components/layout/Header.vue');
    });

    it('resolves Header even when the active slug is unknown', () => {
        const key = findThemeViewKey(modules, ['missing-theme'], 'components/Header');
        expect(key).toContain('Header.vue');
    });
});
