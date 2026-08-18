import { createI18n, type Composer } from 'vue-i18n';
import config, { type LocaleConfig } from '@/engine/i18n/config';
import en from '@/engine/i18n/messages/en';
import id from '@/engine/i18n/messages/id';
import su from '@/engine/i18n/messages/su';

const availableCodes = () => config.availableLocales.map((l: LocaleConfig) => l.code);

/** Map API/browser codes (e.g. en-US) to a bundled vue-i18n locale. */
export const normalizeLocaleCode = (code: string): string => {
    const trimmed = (code || '').trim();
    if (!trimmed) return config.locale;
    if (availableCodes().includes(trimmed)) return trimmed;
    const base = trimmed.split('-')[0]?.toLowerCase() ?? '';
    if (base && availableCodes().includes(base)) return base;
    return 'en';
};

const getComposer = (): Composer => i18n.global as unknown as Composer;

/**
 * Detect the best locale to use
 * Priority: 1. localStorage, 2. Browser language, 3. Default
 */
const detectLocale = (): string => {
    const savedLocale = localStorage.getItem('locale');
    if (savedLocale && config.availableLocales.some((l: LocaleConfig) => l.code === savedLocale)) {
        return savedLocale;
    }
    return config.locale;
};

const detectedLocale = detectLocale();

const messages = {
    en,
    id,
    su,
};

const i18n = createI18n({
    ...config,
    locale: detectedLocale,
    messages,
});

if (!localStorage.getItem('locale')) {
    localStorage.setItem('locale', detectedLocale);
}

export default i18n;

export const setLocale = (locale: string) => {
    const resolved = normalizeLocaleCode(locale);
    getComposer().locale.value = resolved;
    localStorage.setItem('locale', resolved);
    document.documentElement.lang = resolved;
};

export const getLocale = () => getComposer().locale.value;

export const getAvailableLocales = () => config.availableLocales;

export const getBrowserLocale = () => {
    const browserLang = navigator.language || (navigator as unknown as { userLanguage: string }).userLanguage;
    const segments = browserLang ? browserLang.split('-') : [];
    return segments[0] ? segments[0].toLowerCase() : null;
};
