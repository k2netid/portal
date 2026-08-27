import type { Router } from 'vue-router';
import type { useNavigationStore } from '@/shared/stores/navigation';
import type { useDashboardStore } from '@/shared/stores/dashboard';
import type { AppModule } from '@/engine/types/module';
import { registry } from '@/engine/registry';
import { logger } from '@/shared/utils/logger';

type NavStore = ReturnType<typeof useNavigationStore>;
type DashboardStore = ReturnType<typeof useDashboardStore>;

type OptionalPackLoader = () => Promise<Record<string, unknown>>;

/**
 * First-party optional modules: FE AppModule id === registry slug.
 * Add CMS packs here when extracted (forms, media, publishing, …).
 * Loader must export either `mailModules` / `{Studly}Modules` or a `modules: AppModule[]` array.
 */
const OPTIONAL_FIRST_PARTY: Array<{
    slug: string;
    load: OptionalPackLoader;
}> = [
    {
        slug: 'mail',
        load: () => import('@/modules/Mail'),
    },
    {
        slug: 'library',
        load: () => import('@/modules/Library'),
    },
    {
        slug: 'publishing',
        load: () => import('@/modules/Publishing'),
    },
    {
        slug: 'media',
        load: () => import('@/modules/Media'),
    },
    {
        slug: 'layout',
        load: () => import('@/modules/Layout'),
    },
    {
        slug: 'forms',
        load: () => import('@/modules/Forms'),
    },
    {
        slug: 'newsletter',
        load: () => import('@/modules/Newsletter'),
    },
    {
        slug: 'analytics',
        load: () => import('@/modules/Analytics'),
    },
    {
        slug: 'search',
        load: () => import('@/modules/Search'),
    },
    {
        slug: 'member',
        load: () => import('@/modules/Member'),
    },
    {
        slug: 'cms-ai',
        load: () => import('@/modules/CmsAi'),
    },
];

function resolveAppModules(mod: Record<string, unknown>, slug: string): AppModule[] {
    if (Array.isArray(mod.modules)) {
        return mod.modules as AppModule[];
    }
    if (Array.isArray(mod.mailModules)) {
        return mod.mailModules as AppModule[];
    }
    const studly = slug
        .split(/[-_]/)
        .map((p) => p.charAt(0).toUpperCase() + p.slice(1))
        .join('');
    const named = mod[`${studly}Modules`];
    if (Array.isArray(named)) {
        return named as AppModule[];
    }
    return [];
}

/**
 * Register optional first-party FE modules when product-active in the registry.
 * Kernel Core modules are registered separately in bootstrapConsoleApp.
 */
export async function registerOptionalFirstPartyModules(activeExtensions: string[]): Promise<string[]> {
    const registered: string[] = [];
    const active = new Set(activeExtensions);

    for (const entry of OPTIONAL_FIRST_PARTY) {
        if (!active.has(entry.slug)) {
            continue;
        }
        if (registry.hasModule(entry.slug)) {
            continue;
        }

        try {
            const mod = await entry.load();
            const modules = resolveAppModules(mod, entry.slug);

            if (modules.length === 0) {
                logger.warning(`[OptionalModules] No AppModule export for slug ${entry.slug}`);
                continue;
            }

            modules.forEach((m) => registry.register(m));
            await registry.initializeModules(modules);
            registered.push(entry.slug);
        } catch (error) {
            logger.error(`[OptionalModules] Failed to register ${entry.slug}`, error);
        }
    }

    return registered;
}

export function scheduleDeferredConsoleModules(
    _router: Router,
    _navStore: NavStore,
    _dbStore: DashboardStore,
) {
    // Optional modules are registered during bootstrap when active_extensions is known.
}

/**
 * After App Store activate: if an optional FE module was newly enabled, a full
 * reload is required so createConsoleRouter can snapshot its routes.
 */
export function optionalModuleNeedsReload(slug: string, wasRegisteredBefore: boolean): boolean {
    return OPTIONAL_FIRST_PARTY.some((e) => e.slug === slug) && !wasRegisteredBefore;
}

export function isOptionalFirstPartySlug(slug: string): boolean {
    return OPTIONAL_FIRST_PARTY.some((e) => e.slug === slug);
}

export function ensureDeferredConsoleModules(
    _router: Router,
    _navStore: NavStore,
    _dbStore: DashboardStore,
): Promise<void> {
    return Promise.resolve();
}
