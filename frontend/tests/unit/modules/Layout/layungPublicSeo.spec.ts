import { describe, expect, it } from 'vitest';
import { resolveLayungPublicSeo } from '@/modules/Layout/views/themes/layung/composables/layungPublicSeo';

const t = (_key: string, fallback: string) => fallback;

describe('layung public SEO', () => {
    it('uses the site name alone on the homepage', () => {
        const seo = resolveLayungPublicSeo({ themePage: 'pages/Home', siteName: 'K2NET', t });
        expect(seo.title).toBe('K2NET');
        expect(seo.description.length).toBeGreaterThan(20);
    });

    it('prefixes inner pages and covers /pricing/isp', () => {
        const seo = resolveLayungPublicSeo({
            themePage: 'pages/PricingIsp',
            siteName: 'K2NET',
            t,
        });
        expect(seo.title).toBe('Paket Internet · K2NET');
    });
});
