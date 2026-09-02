import '@/styles/shell/console-tailwind.css';
import i18n from '@/engine/i18n';
import { setAppShell } from '@/config/shell';
import {
    createShellApp,
    initShellLayout,
    useLoggerPlugin,
} from './main-shared';

setAppShell('public');

document.title = i18n.global.t('system.app.publicTitle', 'K2NET');

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initShellLayout('public'));
} else {
    initShellLayout('public');
}

async function bootstrap(): Promise<void> {
    const { default: PublicApp } = await import('./PublicApp.vue');
    const app = createShellApp(PublicApp);
    const { logger } = await useLoggerPlugin(app);

    let active: string[] = [];
    try {
        const { useSystemStore } = await import('@/modules/Core/System/stores/system');
        const systemStore = useSystemStore();
        await systemStore.fetchPublicSettings();
        active = systemStore.activeExtensions ?? [];
    } catch {
        /* theme runtime still mounts without public settings */
    }

    if (active.includes('member')) {
        try {
            const { useMemberStore } = await import('@/modules/Member/stores/member');
            await useMemberStore().hydrate();
        } catch {
            /* public visitors can browse as guests */
        }
    }

    const { createPublicRouter } = await import('@/engine/router/public');
    const router = createPublicRouter();

    if (active.includes('member')) {
        const { registerMemberAreaContributions, appendMemberPortalRoutes } = await import('@/engine/memberArea/MemberAreaRegistry');
        const { coreMemberAreaContribution } = await import('@/modules/Member/memberArea');
        const contributions = [coreMemberAreaContribution];

        if (active.includes('publishing')) {
            const { publishingMemberAreaContribution } = await import('@/modules/Publishing/memberArea');
            contributions.push(publishingMemberAreaContribution);
        }

        if (active.includes('newsletter')) {
            const { newsletterMemberAreaContribution } = await import('@/modules/Newsletter/memberArea');
            contributions.push(newsletterMemberAreaContribution);
        }

        if (active.includes('forms')) {
            const { formsMemberAreaContribution } = await import('@/modules/Forms/memberArea');
            contributions.push(formsMemberAreaContribution);
        }

        registerMemberAreaContributions(contributions);
        appendMemberPortalRoutes(router, active);
    }

    app.use(router);

    if (active.includes('analytics')) {
        try {
            const { installPublicAnalytics } = await import('@/modules/Analytics/composables/usePublicAnalytics');
            installPublicAnalytics(router);
        } catch {
            /* analytics pack optional */
        }
    }

    logger.info('[SPA] Mounting public theme runtime');
    app.mount('#app');
}

void bootstrap();
