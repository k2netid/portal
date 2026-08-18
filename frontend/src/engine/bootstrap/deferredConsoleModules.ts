import type { Router } from 'vue-router';
import type { AppModule } from '@/engine/types/module';
import { registry } from '@/engine/registry';
import { logger } from '@/shared/utils/logger';
import { consolePathNeedsContentModules } from '@/modules/bootstrap/content';
import type { useNavigationStore } from '@/shared/stores/navigation';
import type { useDashboardStore } from '@/shared/stores/dashboard';

type NavStore = ReturnType<typeof useNavigationStore>;
type DashboardStore = ReturnType<typeof useDashboardStore>;

const DASHBOARD_PARENT = 'dashboard';

let deferredInstallPromise: Promise<void> | null = null;

function registerModuleRoutes(router: Router, modules: AppModule[]) {
    for (const module of modules) {
        for (const route of module.routes ?? []) {
            const name = route.name;
            if (typeof name === 'string' && router.hasRoute(name)) {
                continue;
            }
            router.addRoute(DASHBOARD_PARENT, route);
        }
    }
}

async function installDeferredConsoleModules(
    router: Router,
    navStore: NavStore,
    dbStore: DashboardStore,
) {
    const toRegister: AppModule[] = [];

    if (!registry.hasModule('publishing')) {
        const { loadContentModules } = await import('@/modules/bootstrap/content');
        toRegister.push(...(await loadContentModules()));
    }
    const { loadDeferredConsoleModules } = await import('@/modules/deferred/console');
    toRegister.push(...(await loadDeferredConsoleModules()));

    if (toRegister.length === 0) {
        return;
    }

    for (const module of toRegister) {
        if (!registry.hasModule(module.id)) {
            registry.register(module);
        }
    }

    await registry.initializeModules(toRegister);
    registerModuleRoutes(router, toRegister);

    for (const module of toRegister) {
        if (module.navigation) {
            navStore.registerModuleNavigation(module.id, module.navigation);
        }
        for (const db of module.dashboards ?? []) {
            dbStore.registerDashboard(db);
        }
    }

    logger.info('[Kernel] Deferred console modules installed.', {
        modules: toRegister.map((m) => m.id),
    });
}

export function scheduleDeferredConsoleModules(
    router: Router,
    navStore: NavStore,
    dbStore: DashboardStore,
) {
    const run = () => {
        if (!deferredInstallPromise) {
            deferredInstallPromise = installDeferredConsoleModules(router, navStore, dbStore).catch(
                (error) => {
                    deferredInstallPromise = null;
                    logger.error('[Kernel] Deferred console module install failed', error);
                },
            );
        }
        return deferredInstallPromise;
    };

    if (typeof window !== 'undefined' && 'requestIdleCallback' in window) {
        window.requestIdleCallback(() => void run(), { timeout: 2500 });
    } else {
        setTimeout(() => void run(), 50);
    }
}

export function ensureDeferredConsoleModules(
    router: Router,
    navStore: NavStore,
    dbStore: DashboardStore,
): Promise<void> {
    const pathname = typeof window !== 'undefined' ? window.location.pathname : '';
    const needsContent = consolePathNeedsContentModules(pathname);
    const contentReady = !needsContent || registry.hasModule('publishing');

    if (contentReady && registry.hasModule('ai')) {
        return Promise.resolve();
    }

    if (!deferredInstallPromise) {
        scheduleDeferredConsoleModules(router, navStore, dbStore);
    }

    return deferredInstallPromise ?? Promise.resolve();
}
