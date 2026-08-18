import { useConsoleContextStore } from '../stores/consoleContext';
import { registry } from '../registry';
import { logger } from '@/shared/utils/logger';

export async function bootstrapPublicApp() {
    logger.info('[Kernel] Initializing public kernel...');

    const consoleStore = useConsoleContextStore();
    consoleStore.initConsoleContext();

    try {
        const { loadCoreModules } = await import('@/modules/bootstrap/core');
        const modules = await loadCoreModules();
        modules.forEach((m) => registry.register(m));
        await registry.initializeModules(modules);
    } catch (error) {
        logger.error('[Kernel] Failed to register public modules during bootstrap', error);
    }

    logger.info('[Kernel] Public kernel ready.');

    return { registry, consoleStore };
}
