import i18n, { ensureModuleLocaleMessages, getLocale, normalizeLocaleCode } from '@/engine/i18n';

export type DeferredLocaleModule = 'security';

const securityLocaleFiles = import.meta.glob<Record<string, unknown>>(
    '/src/modules/Core/Security/locales/*.json',
    { import: 'default' },
);
const loadedSecurityLocales = new Set<string>();
const securityLocaleLoads = new Map<string, Promise<void>>();

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
    if (!modules.includes('security')) {
        return;
    }

    const activeLocale = normalizeLocaleCode(getLocale());
    const locales = activeLocale === 'id' ? ['id'] : [activeLocale, 'id'];
    await Promise.all(locales.map(async (locale) => {
        if (loadedSecurityLocales.has(locale)) return;

        const existing = securityLocaleLoads.get(locale);
        if (existing) {
            await existing;
            return;
        }

        const path = `/src/modules/Core/Security/locales/${locale}.json`;
        const loader = securityLocaleFiles[path];
        if (!loader) {
            throw new Error(`[i18n] Missing security locale catalog: ${path}`);
        }

        const pending = (async () => {
            const messages = await loader();
            mergeForLocale(locale as 'en' | 'id' | 'su', 'security', messages);
            loadedSecurityLocales.add(locale);
        })();
        securityLocaleLoads.set(locale, pending);
        try {
            await pending;
        } catch (error) {
            securityLocaleLoads.delete(locale);
            throw error;
        }
    }));
}

export async function preloadConsoleModuleLocales(): Promise<void> {
    void ensureDeferredLocales(['security']);
}

export async function preloadLocalesForRoute(
    path: string,
    _name?: string | symbol | null,
    extension?: string,
): Promise<void> {
    const routePath = path.toLowerCase();
    const modules: DeferredLocaleModule[] = [];
    const moduleIds = new Set<string>();

    if (extension) {
        moduleIds.add(extension);
    }

    if (moduleIds.size > 0) {
        await ensureModuleLocaleMessages([...moduleIds]);
    }

    if (routePath.includes('security') || routePath.includes('profile')) {
        modules.push('security');
    }

    if (modules.length > 0) {
        await ensureDeferredLocales(modules);
    }
}
