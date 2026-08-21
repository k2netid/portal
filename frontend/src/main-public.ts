import '@/styles/shell/public-tailwind.css';
import i18n from '@/engine/i18n';
import { bootstrapPublicApp } from '@/engine/bootstrap/public';
import {
    createShellApp,
    initShellLayout,
    useLoggerPlugin,
} from './main-shared';

document.title = i18n.global.t('common.labels.app.consoleDefaultTitle');

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initShellLayout('public'));
} else {
    initShellLayout('public');
}

async function bootstrap(): Promise<void> {
    const { default: PublicApp } = await import('./PublicApp.vue');
    const app = createShellApp(PublicApp);
    const { logger } = await useLoggerPlugin(app);

    await bootstrapPublicApp();

    const { default: router } = await import('@/engine/router/public');
    app.use(router);

    const { useNavigationStore } = await import('@/shared/stores/navigation');
    const { useDashboardStore } = await import('@/shared/stores/dashboard');
    const { registry } = await import('@/engine/registry');

    const navStore = useNavigationStore();
    const dbStore = useDashboardStore();

    Object.entries(registry.getNavigation()).forEach(([id, navs]) => {
        navStore.registerModuleNavigation(id, navs);
    });

    registry.getDashboards().forEach((db) => dbStore.registerDashboard(db));

    logger.info('[SPA] Mounting public shell');
    app.mount('#app');
}

void bootstrap();
