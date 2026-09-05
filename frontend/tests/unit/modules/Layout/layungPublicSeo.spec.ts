import { describe, expect, it } from 'vitest';
import { resolveLayungPublicSeo } from '@/modules/Layout/views/themes/layung/composables/layungPublicSeo';

const t = (_key: string, fallback: string) => fallback;

describe('layung public SEO', () => {
    it('uses the site name alone on the homepage', () => {
        const seo = resolveLayungPublicSeo({ themePage: 'pages/Home', siteName: 'Portal', t });
        expect(seo.title).toBe('Portal');
        expect(seo.description.length).toBeGreaterThan(20);
    });

    it('prefixes inner pages and covers /pricing/isp', () => {
        const seo = resolveLayungPublicSeo({
            themePage: 'pages/PricingIsp',
            siteName: 'Portal',
            t,
        });
        expect(seo.title).toBe('Paket Internet · Portal');
    });
});
