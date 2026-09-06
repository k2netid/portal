import '@/styles/shell/console-tailwind.css';
import i18n from '@/engine/i18n';
import { bootstrapConsoleApp } from '@/engine/bootstrap/console';
import { scheduleDeferredConsoleModules } from '@/engine/bootstrap/deferredConsoleModules';
import {
    createShellApp,
    initShellLayout,
    useLoggerPlugin,
} from './main-shared';

document.title = i18n.global.t('system.app.consoleTitle', 'Console');

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
        await navStore.fetchConsoleMenus();
    } else {
        navStore.markMenusReady();
    }

    registry.getDashboards().forEach((db) => dbStore.registerDashboard(db));

    await Promise.all([
        import('@/styles/console.css'),
        import('@/styles/console-presets.css'),
        import('@/styles/editor.css'),
    ]);

    logger.info('[SPA] Mounting console kernel');
    app.mount('#app');

    scheduleDeferredConsoleModules(router, navStore, dbStore);
}

void bootstrap();
