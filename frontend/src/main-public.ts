import '@/styles/shell/console-tailwind.css';
import i18n from '@/engine/i18n';
import { setAppShell } from '@/config/shell';
import {
    createShellApp,
    initShellLayout,
    useLoggerPlugin,
} from './main-shared';

setAppShell('public');

document.title = i18n.global.t('system.app.publicTitle', 'Jejakawan');

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initShellLayout('public'));
} else {
    initShellLayout('public');
}

async function bootstrap(): Promise<void> {
    const { default: PublicApp } = await import('./PublicApp.vue');
    const app = createShellApp(PublicApp);
    const { logger } = await useLoggerPlugin(app);

    try {
        const { useSystemStore } = await import('@/modules/Core/System/stores/system');
        await useSystemStore().fetchPublicSettings();
    } catch {
        /* theme runtime still mounts without public settings */
    }

    try {
        const { useMemberStore } = await import('@/modules/Member/stores/member');
        await useMemberStore().hydrate();
    } catch {
        /* public visitors can browse as guests */
    }

    const { createPublicRouter } = await import('@/engine/router/public');
    const router = createPublicRouter();
    app.use(router);

    try {
        const { installPublicAnalytics } = await import('@/modules/Analytics/composables/usePublicAnalytics');
        installPublicAnalytics(router);
    } catch {
        /* analytics pack optional */
    }

    logger.info('[SPA] Mounting public theme runtime');
    app.mount('#app');
}

void bootstrap();
