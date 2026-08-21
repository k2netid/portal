import '@/styles/shell/console-tailwind.css';
import i18n from '@/engine/i18n';
import { bootstrapConsoleApp } from '@/engine/bootstrap/console';
import apiClient from '@/engine/api/client';
import {
    createShellApp,
    initShellLayout,
    useLoggerPlugin,
} from './main-shared';

document.title = i18n.global.t('system.app.consoleTitle', 'Jejakawan Console');

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initShellLayout('console'));
} else {
    initShellLayout('console');
}

async function bootstrap(): Promise<void> {
    const { default: ConsoleApp } = await import('./ConsoleApp.vue');
    const app = createShellApp(ConsoleApp);
    const { logger } = await useLoggerPlugin(app);

    const { registry, authStore } = await bootstrapConsoleApp();

    void import('@/engine/i18n/deferredLocales').then((m) => m.preloadConsoleModuleLocales());

    const router = (await import('@/engine/router/console')).createConsoleRouter();
    app.use(router);

    const { useNavigationStore } = await import('@/shared/stores/navigation');
    const { useDashboardStore } = await import('@/shared/stores/dashboard');

    const navStore = useNavigationStore();
    const dbStore = useDashboardStore();

    Object.entries(registry.getNavigation()).forEach(([id, navs]) => {
        navStore.registerModuleNavigation(id, navs);
    });

    if (authStore.isAuthenticated) {
        void apiClient
            .get('/manage/infra/extensions/navigation')
            .then((dynamicNavs) => {
                if (Array.isArray(dynamicNavs.data)) {
                    navStore.registerModuleNavigation('dynamic_plugins', dynamicNavs.data);
                }
            })
            .catch((error) => {
                logger.warning('[App] Failed to load dynamic plugin navigation', error);
            });
    }

    registry.getDashboards().forEach((db) => dbStore.registerDashboard(db));

    await Promise.all([
        import('@/styles/console.css'),
        import('@/styles/console-presets.css'),
        import('@/styles/editor.css'),
    ]);

    logger.info('[SPA] Mounting console kernel');
    app.mount('#app');

    const { scheduleDeferredConsoleModules } = await import('@/engine/bootstrap/deferredConsoleModules');
    scheduleDeferredConsoleModules(router, navStore, dbStore);
}

void bootstrap();
