import i18n from '@/engine/i18n';

export type DeferredLocaleModule = 'security' | 'content' | 'intelligence';

const loaded = new Set<DeferredLocaleModule>();

const loaderImporters: Record<DeferredLocaleModule, () => Promise<() => Promise<{ en: Record<string, unknown>; id: Record<string, unknown> }>>> = {
    security: () => import('./loaders/security').then((m) => m.default),
    content: () => import('./loaders/content').then((m) => m.default),
    intelligence: () => import('./loaders/intelligence').then((m) => m.default),
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

    if (key === 'intelligence') {
        const system = (current.system as Record<string, unknown> | undefined) ?? {};
        const { analytics, ...rest } = messages as { analytics?: Record<string, unknown> };
        composer.mergeLocaleMessage(locale, {
            ...rest,
            system: analytics ? { ...system, analytics } : system,
        });
        return;
    }

    if (key === 'content') {
        composer.mergeLocaleMessage(locale, messages);
        return;
    }

    composer.mergeLocaleMessage(locale, { [key]: messages });
}

export async function ensureDeferredLocales(modules: DeferredLocaleModule[]): Promise<void> {
    const pending = modules.filter((m) => !loaded.has(m));
    if (pending.length === 0) {
        return;
    }

    await Promise.all(
        pending.map(async (key) => {
            const loadBundle = await loaderImporters[key]();
            const bundle = await loadBundle();
            mergeForLocale('en', key, bundle.en);
            mergeForLocale('id', key, bundle.id);
            if ('su' in bundle) {
                mergeForLocale('su', key, (bundle as any).su);
            }
            loaded.add(key);
        }),
    );
}

export async function preloadConsoleModuleLocales(): Promise<void> {
    void ensureDeferredLocales(['security']);
}

export async function preloadLocalesForRoute(path: string, name?: string | symbol | null): Promise<void> {
    const routePath = path.toLowerCase();
    const routeName = String(name ?? '').toLowerCase();
    const modules: DeferredLocaleModule[] = [];

    if (routePath.includes('security') || routePath.includes('security')) {
        modules.push('security');
    }
    if (
        // Module base paths
        routePath.includes('/publishing') ||
        routePath.includes('/media') ||
        routePath.includes('/library') ||
        routePath.includes('/forms') ||
        routePath.includes('/layout') ||
        routePath.includes('site-editor') ||
        routePath.includes('builder') ||
        // Publishing routes
        routePath.includes('/contents') ||
        routePath.includes('/categories') ||
        routePath.includes('/content-templates') ||
        routePath.includes('/comments') ||
        routePath.includes('/seo') ||
        // Layout routes
        routePath.includes('/themes') ||
        routePath.includes('/menus') ||
        routePath.includes('/widgets') ||
        routePath.includes('/redirects') ||
        // Library routes
        routePath.includes('/custom-fields') ||
        routePath.includes('/tags') ||
        // Fallback checks
        routeName.includes('publishing') ||
        routeName.includes('content') ||
        routeName.includes('builder') ||
        routeName.includes('layout')
    ) {
        modules.push('content');
    }
    if (
        routePath.includes('/newsletter')
        || routePath.includes('/search')
        || routePath.includes('/ai')
        || routePath.includes('/analytics')
        || routeName.includes('intelligence')
    ) {
        modules.push('intelligence');
    }

    if (modules.length > 0) {
        await ensureDeferredLocales(modules);
    }
}
