import { describe, expect, it } from 'vitest';
import { lookupModuleLocale, looksLikeI18nPath } from '@/modules/Layout/customizer/i18n/lookupModuleLocale';
import { translateCustomizerNavKey } from '@/modules/Layout/customizer/i18n/translateCustomizerNavKey';

describe('lookupModuleLocale', () => {
    it('resolves Layung customizer item keys from theme locale JSON', () => {
        expect(lookupModuleLocale('id', 'theme.layung.customizer.items.hero_section')).toBe('Bagian Hero');
        expect(lookupModuleLocale('en', 'theme.layung.customizer.items.isp_bento')).toBe('Three Business Lines');
        expect(lookupModuleLocale('id', 'theme.layung.customizer.sidebar.page_about')).toBe('Halaman Tentang Kami');
        expect(lookupModuleLocale('id', 'theme.layung.customizer.items.contact_form_slug')).toBe('Slug formulir kontak');
    });

    it('falls back to id when the locale pack misses a key', () => {
        expect(lookupModuleLocale('xx', 'theme.layung.customizer.items.footer_section')).toBe('Footer');
    });
});

describe('translateCustomizerNavKey', () => {
    const missingT = (key: string) => key;

    it('does not leak raw theme.layung paths when vue-i18n misses', () => {
        expect(translateCustomizerNavKey(missingT, 'theme.layung.customizer.items.hero_section', 'en')).toBe(
            'Hero Section',
        );
        expect(translateCustomizerNavKey(missingT, 'theme.layung.customizer.sidebar.page_contact', 'id')).toBe(
            'Halaman Kontak',
        );
        expect(
            looksLikeI18nPath(
                translateCustomizerNavKey(missingT, 'theme.layung.customizer.items.packages_section', 'id'),
            ),
        ).toBe(false);
    });

    it('humanizes the tail if the key is unknown', () => {
        expect(translateCustomizerNavKey(missingT, 'theme.layung.customizer.items.not_a_real_key', 'id')).toBe(
            'not a real key',
        );
    });
});
