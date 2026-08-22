import type { Router } from 'vue-router';
import type { useNavigationStore } from '@/shared/stores/navigation';
import type { useDashboardStore } from '@/shared/stores/dashboard';
import type { AppModule } from '@/engine/types/module';
import { registry } from '@/engine/registry';
import { logger } from '@/shared/utils/logger';

type NavStore = ReturnType<typeof useNavigationStore>;
type DashboardStore = ReturnType<typeof useDashboardStore>;

/** First-party optional modules: FE AppModule id === registry slug. */
const OPTIONAL_FIRST_PARTY: Array<{
    slug: string;
    load: () => Promise<{ mailModules: AppModule[] }>;
}> = [
    {
        slug: 'mail',
        load: () => import('@/modules/Mail'),
    },
];

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
            const modules = mod.mailModules ?? [];

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
