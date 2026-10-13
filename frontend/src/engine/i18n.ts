import { createI18n, type Composer } from 'vue-i18n';
import config, { type LocaleConfig } from '@/engine/i18n/config';
import { resolvePreferredLocale } from '@/engine/i18n/resolvePreferredLocale';
import id from '@/engine/i18n/messages/id';

type LocaleMessages = Record<string, unknown>;
type LocaleJsonLoader = () => Promise<LocaleMessages>;

const moduleLocaleFiles = import.meta.glob<LocaleMessages>('/src/modules/**/locales/**/*.json', {
    import: 'default',
});

const moduleLocaleDefinitions: Record<string, { directory: string; namespace: string }> = {
    system: { directory: 'Core/System', namespace: 'system' },
    infra: { directory: 'Core/Infra', namespace: 'infra' },
    mail: { directory: 'Mail', namespace: 'mail' },
    library: { directory: 'Library', namespace: 'library' },
    publishing: { directory: 'Publishing', namespace: 'publishing' },
    media: { directory: 'Media', namespace: 'media' },
    layout: { directory: 'Layout', namespace: 'layout' },
    forms: { directory: 'Forms', namespace: 'forms' },
    search: { directory: 'Search', namespace: 'search' },
    'cms-ai': { directory: 'CmsAi', namespace: 'ai' },
    member: { directory: 'Member', namespace: 'member' },
    newsletter: { directory: 'Newsletter', namespace: 'newsletter' },
};

const moduleLocaleDependencies: Record<string, string[]> = {
    layout: ['publishing'],
};

const moduleLocaleLoads = new Map<string, Promise<void>>();
const activeModuleLocales = new Set<string>();

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

export const getConsoleDefaultLocale = (): string | null => {
    if (typeof window === 'undefined') return null;
    try {
        const raw = localStorage.getItem('ja_console_default_locale');
        if (raw) return raw;
    } catch {
        // ignore
    }
    return null;
};

/**
 * Detect the best locale to use.
 * Priority: 1. localStorage (user choice), 2. Console default locale setting, 3. Theme default locale setting, 4. Browser language (if auto), 5. Fallback ('id')
 */
const detectLocale = (): string => {
    const consoleDefault = getConsoleDefaultLocale();
    const themeDefault = getThemeDefaultLocale();
    const effectiveDefault = consoleDefault || themeDefault;
    const isAuto = effectiveDefault === 'auto';
    const fallbackCode = (!isAuto && availableCodes().includes(effectiveDefault)) ? effectiveDefault : (config.locale || 'id');
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
let detectedLocaleReady: Promise<void> = Promise.resolve();

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

async function loadModuleLocaleFile(path: string): Promise<LocaleMessages> {
    const key = `/src/modules/${path}`;
    const loader = moduleLocaleFiles[key] as LocaleJsonLoader | undefined;
    if (!loader) {
        throw new Error(`[i18n] Missing locale catalog: ${key}`);
    }
    return loader();
}

async function mergeModuleLocale(moduleId: string, locale: string): Promise<void> {
    const definition = moduleLocaleDefinitions[moduleId];
    if (!definition) return;

    const pack = await loadModuleLocaleFile(`${definition.directory}/locales/${locale}.json`);
    if (moduleId === 'mail') {
        const mail = pack.mail && typeof pack.mail === 'object'
            ? pack.mail as LocaleMessages
            : {};
        const flattened = {
            ...mail,
            navigationMenuMail: pack.navigationMenuMail,
        };
        getComposer().mergeLocaleMessage(locale, { [definition.namespace]: flattened });
        return;
    }

    getComposer().mergeLocaleMessage(locale, { [definition.namespace]: pack });

    if (moduleId === 'layout') {
        const [layung, janari, sarangenge, sareupna] = await Promise.all([
            loadModuleLocaleFile(`Layout/views/themes/layung/locales/${locale}.json`),
            loadModuleLocaleFile(`Layout/views/themes/janari/locales/${locale}.json`),
            loadModuleLocaleFile(`Layout/views/themes/sarangenge/locales/${locale}.json`),
            loadModuleLocaleFile(`Layout/views/themes/sareupna/locales/${locale}.json`),
        ]);
        getComposer().mergeLocaleMessage(locale, {
            theme: { layung, janari, sarangenge, sareupna },
        });
    }
}

/** Load only the locale catalogs required by the active shell or route. */
export async function ensureModuleLocaleMessages(moduleIds: string[], locale?: string): Promise<void> {
    await detectedLocaleReady;
    const resolved = normalizeLocaleCode(locale ?? getComposer().locale.value);
    await loadLocaleMessages(resolved);

    const requiredModules = new Set<string>();
    const addModuleAndDependencies = (moduleId: string): void => {
        if (!moduleLocaleDefinitions[moduleId]) return;
        if (requiredModules.has(moduleId)) return;
        requiredModules.add(moduleId);
        for (const dependency of moduleLocaleDependencies[moduleId] ?? []) {
            addModuleAndDependencies(dependency);
        }
    };
    moduleIds.forEach(addModuleAndDependencies);

    await Promise.all([...requiredModules].map(async (moduleId) => {
        activeModuleLocales.add(moduleId);

        const cacheKey = `${resolved}:${moduleId}`;
        const existing = moduleLocaleLoads.get(cacheKey);
        if (existing) {
            await existing;
            return;
        }

        const pending = mergeModuleLocale(moduleId, resolved);
        moduleLocaleLoads.set(cacheKey, pending);
        try {
            await pending;
        } catch (error) {
            moduleLocaleLoads.delete(cacheKey);
            throw error;
        }
    }));
}

if (detectedLocale !== 'id') {
    detectedLocaleReady = loadLocaleMessages(detectedLocale).then(() => {
        getComposer().locale.value = detectedLocale;
        document.documentElement.lang = detectedLocale;
    });
} else {
    document.documentElement.lang = 'id';
}

export default i18n;

export const setLocale = (locale: string): void => {
    const resolved = normalizeLocaleCode(locale);
    void ensureModuleLocaleMessages([...activeModuleLocales], resolved).then(() => {
        getComposer().locale.value = resolved;
    });
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
