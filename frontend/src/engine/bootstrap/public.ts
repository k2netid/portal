import { hasStatefulSessionCookie } from '@/engine/api/client';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useConsoleContextStore } from '../stores/consoleContext';
import { registry } from '../registry';
import { logger } from '@/shared/utils/logger';

export async function bootstrapPublicApp() {
    logger.info('[Kernel] Initializing public kernel...');

    const authStore = useAuthStore();
    const consoleStore = useConsoleContextStore();

    consoleStore.initConsoleContext();
    authStore.initAuth();

    try {
        const me = await authStore.fetchUser({ skipCsrfRefresh: !hasStatefulSessionCookie() });
        if (!me.success) {
            authStore.clearAuth();
        }
    } catch {
        authStore.clearAuth();
    } finally {
        authStore.authBootstrapComplete = true;
    }

    try {
        const { loadCoreModules } = await import('@/modules/bootstrap/core');
        const modules = await loadCoreModules();
        modules.forEach((m) => registry.register(m));
        await registry.initializeModules(modules);
    } catch (error) {
        logger.error('[Kernel] Failed to register public modules during bootstrap', error);
    }

    logger.info('[Kernel] Public kernel ready.', {
        isAuthenticated: authStore.isAuthenticated,
    });

    return { registry, consoleStore, authStore };
}
