import i18n from '@/engine/i18n';

export type DeferredLocaleModule = 'security';

const loaded = new Set<DeferredLocaleModule>();

const loaderImporters: Record<DeferredLocaleModule, () => Promise<() => Promise<{ en: Record<string, unknown>; id: Record<string, unknown> }>>> = {
    security: () => import('./loaders/security').then((m) => m.default),
};

function mergeForLocale(locale: 'en' | 'id' | 'su', key: DeferredLocaleModule, messages: Record<string, unknown>): void {
    const composer = i18n.global;
    const current = composer.getLocaleMessage(locale) as Record<string, unknown>;

    if (key === 'security') {
        const system = (current.system as Record<string, unknown> | undefined) ?? {};
        composer.mergeLocaleMessage(locale, {
            system: { ...system, security: messages },
            security: messages,
        });
        return;
    }

    composer.mergeLocaleMessage(locale, { [key]: messages });
}

export async function ensureDeferredLocales(modules: DeferredLocaleModule[]): Promise<void> {
    const pending = modules.filter((m) => !loaded.has(m));
    if (pending.length === 0) {
        return;
    }

    const localeRef = i18n.global.locale;
    const currentLocale = (typeof localeRef === 'string' ? localeRef : (localeRef as { value: string }).value) as 'en' | 'id' | 'su';

    await Promise.all(
        pending.map(async (key) => {
            const loadBundle = await loaderImporters[key]();
            const bundle = (await loadBundle()) as Record<string, Record<string, unknown>>;
            if (currentLocale && bundle[currentLocale]) {
                mergeForLocale(currentLocale, key, bundle[currentLocale]);
            } else if (bundle.id) {
                mergeForLocale('id', key, bundle.id);
            }
            loaded.add(key);
        }),
    );
}

export async function preloadConsoleModuleLocales(): Promise<void> {
    void ensureDeferredLocales(['security']);
}

export async function preloadLocalesForRoute(path: string, _name?: string | symbol | null): Promise<void> {
    const routePath = path.toLowerCase();
    const modules: DeferredLocaleModule[] = [];

    if (routePath.includes('security')) {
        modules.push('security');
    }

    if (modules.length > 0) {
        await ensureDeferredLocales(modules);
    }
}
