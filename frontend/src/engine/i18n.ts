import { createI18n, type Composer } from 'vue-i18n';
import config, { type LocaleConfig } from '@/engine/i18n/config';
import { resolvePreferredLocale } from '@/engine/i18n/resolvePreferredLocale';
import id from '@/engine/i18n/messages/id';

const availableCodes = () => config.availableLocales.map((l: LocaleConfig) => l.code);

/** Map API/browser codes (e.g. en-US) to a bundled vue-i18n locale. */
export const normalizeLocaleCode = (code: string): string => {
    const trimmed = (code || '').trim();
    if (!trimmed) return config.locale;
    if (availableCodes().includes(trimmed)) return trimmed;
    const base = trimmed.split('-')[0]?.toLowerCase() ?? '';
    if (base && availableCodes().includes(base)) return base;
    return config.locale || 'id';
};

const getComposer = (): Composer => i18n.global as unknown as Composer;

export const getThemeDefaultLocale = (): string => {
    if (typeof window === 'undefined') return 'id';
    try {
        const raw = localStorage.getItem('ja_theme_default_locale');
        if (raw) return raw;
    } catch {
        // ignore
    }
    return 'id';
};

/**
 * Detect the best locale to use.
 * Priority: 1. localStorage (user choice), 2. Theme default locale setting, 3. Browser language (if auto), 4. Fallback ('id')
 */
const detectLocale = (): string => {
    const themeDefault = getThemeDefaultLocale();
    const isAuto = themeDefault === 'auto';
    const fallbackCode = (!isAuto && availableCodes().includes(themeDefault)) ? themeDefault : (config.locale || 'id');
    return resolvePreferredLocale(availableCodes(), {
        stored: localStorage.getItem('locale'),
        fallback: fallbackCode,
        detectBrowser: isAuto,
    });
};

const detectedLocale = detectLocale();

// Lazy loaders for on-demand locale loading
const localeLoaders: Record<string, () => Promise<{ default: Record<string, unknown> }>> = {
    en: () => import('@/engine/i18n/messages/en'),
    id: () => Promise.resolve({ default: id as Record<string, unknown> }),
    su: () => import('@/engine/i18n/messages/su'),
};

// Track which locales are registered in vue-i18n
const loadedLocales = new Set<string>(['id']);

// Bootstrap with the primary locale to keep initial bundle compact and eliminate main-thread deepCopy overhead
const initialMessages = {
    id,
};

const i18n = createI18n({
    ...config,
    // Always boot with the catalog that is already inlined. Setting `en`/`su`
    // before their lazy packs load makes t() return raw keys (customizer sidebar).
    locale: 'id',
    messages: initialMessages,
});

export const loadLocaleMessages = async (locale: string): Promise<void> => {
    const resolved = normalizeLocaleCode(locale);
    if (loadedLocales.has(resolved)) {
        return;
    }
    const loader = localeLoaders[resolved];
    if (loader) {
        const bundle = await loader();
        getComposer().setLocaleMessage(resolved, bundle.default);
        loadedLocales.add(resolved);
    }
};

if (detectedLocale !== 'id') {
    void loadLocaleMessages(detectedLocale).then(() => {
        getComposer().locale.value = detectedLocale;
        document.documentElement.lang = detectedLocale;
    });
} else {
    document.documentElement.lang = 'id';
}

export default i18n;

export const setLocale = (locale: string): void => {
    const resolved = normalizeLocaleCode(locale);
    if (!loadedLocales.has(resolved)) {
        void loadLocaleMessages(resolved).then(() => {
            getComposer().locale.value = resolved;
        });
    } else {
        getComposer().locale.value = resolved;
    }
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
