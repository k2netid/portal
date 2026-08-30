import { createI18n } from 'vue-i18n';
import config from '@/engine/i18n/config';
import { resolvePreferredLocale } from '@/engine/i18n/resolvePreferredLocale';
import en from '@/locales/en/landing.json';
import id from '@/locales/id/landing.json';
import su from '@/locales/su/landing.json';

const available = config.availableLocales.map((locale) => locale.code);

const stored = typeof localStorage !== 'undefined' ? localStorage.getItem('locale') : null;

export const landingLocale = resolvePreferredLocale(available, {
    stored,
    fallback: config.locale,
});

const landingI18n = createI18n({
    legacy: false,
    locale: landingLocale,
    fallbackLocale: config.fallbackLocale,
    messages: {
        en: { landing: en },
        id: { landing: id },
        su: { landing: su },
    },
    fallbackWarn: false,
    missingWarn: false,
});

if (typeof document !== 'undefined') {
    document.documentElement.lang = landingLocale;
}

export default landingI18n;
