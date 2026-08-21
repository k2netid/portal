import { hasStatefulSessionCookie } from '@/engine/api/client';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useConsoleContextStore } from '../stores/consoleContext';
import { registry } from '../registry';
import { logger } from '@/shared/utils/logger';

export async function bootstrapConsoleApp() {
    logger.info('[Kernel] Initializing console kernel...');

    const authStore = useAuthStore();
    const consoleStore = useConsoleContextStore();

    consoleStore.initConsoleContext();

    authStore.initAuth();

    try {
        // Always attempt fetchUser — the session cookie may not be visible to JS
        // (HttpOnly) even when the session is valid. Let the server decide.
        const me = await authStore.fetchUser({ skipCsrfRefresh: !hasStatefulSessionCookie() });
        if (!me.success) {
            authStore.clearAuth();
        }
    } catch (e) {
        authStore.clearAuth();
        logger.warning('[Kernel] No active session found or profile fetch failed.');
    } finally {
        authStore.authBootstrapComplete = true;
    }

    try {
        const { coreModules } = await import('@/modules/Core');
        coreModules.forEach((m) => registry.register(m));
        await registry.initializeModules(coreModules);
    } catch (error) {
        logger.error('[Kernel] Failed to register core console modules during bootstrap', error);
    }

    logger.info('[Kernel] Console kernel ready.', {
        context: consoleStore.context,
        isAuthenticated: authStore.isAuthenticated,
    });

    return { authStore, consoleStore, registry };
}
