import { describe, expect, it } from 'vitest';
import {
    BUNDLED_FRONTEND_THEME_SLUGS,
    buildThemeViewResolveCandidates,
    findThemeViewKey,
} from '@/modules/Layout/utils/themeViewResolver';

const modules = {
    '/src/modules/Layout/views/themes/Index.vue': () => Promise.resolve({}),
    '/src/modules/Layout/views/themes/ThemeCustomizer.vue': () => Promise.resolve({}),
    '/src/modules/Layout/views/themes/sarangenge/components/layout/Header.vue': () => Promise.resolve({}),
    '/src/modules/Layout/views/themes/sarangenge/components/layout/Footer.vue': () => Promise.resolve({}),
    '/src/modules/Layout/views/themes/sarangenge/pages/Home.vue': () => Promise.resolve({}),
    '/src/modules/Layout/views/themes/layung/pages/PricingIsp.vue': () => Promise.resolve({}),
};

describe('themeViewResolver', () => {
    it('falls back to bundled janari/sarangenge when theme slug is missing', () => {
        expect(buildThemeViewResolveCandidates(null)).toEqual([...BUNDLED_FRONTEND_THEME_SLUGS]);
        expect(BUNDLED_FRONTEND_THEME_SLUGS[0]).toBe('janari');
        expect(buildThemeViewResolveCandidates({ name: 'X', slug: '', type: 'frontend' })).toEqual([
            ...BUNDLED_FRONTEND_THEME_SLUGS,
        ]);
    });

    it('isolates candidate themes strictly to [slug, parent_theme] and never leaks peer themes', () => {
        const candidates = buildThemeViewResolveCandidates({
            name: 'Sarangenge',
            slug: 'sarangenge',
            parent_theme: 'janari',
            type: 'frontend',
        });
        expect(candidates).toEqual(['sarangenge', 'janari']);
        expect(candidates).not.toContain('layung');
    });

    it('resolves components/Header from sarangenge when only sarangenge views exist', () => {
        const key = findThemeViewKey(modules, ['sarangenge'], 'components/Header');
        expect(key).toContain('sarangenge/components/layout/Header.vue');
    });

    it('does NOT leak layung page to sarangenge when searching for layung-only page', () => {
        const candidates = buildThemeViewResolveCandidates({
            name: 'Sarangenge',
            slug: 'sarangenge',
            parent_theme: 'janari',
            type: 'frontend',
        });
        const key = findThemeViewKey(modules, candidates, 'pages/PricingIsp');
        expect(key).toBeUndefined();
    });
});
