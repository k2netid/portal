import type { AppModule, DashboardConfig } from '../types/module';
import type { RouteRecordRaw } from 'vue-router';
import type { NavItem } from '@/shared/utils/navigation';
import { logger } from '@/shared/utils/logger';

class ModuleRegistry {
    private modules: Map<string, AppModule> = new Map();

    /**
     * Register a new module to the system.
     */
    public hasModule(id: string): boolean {
        return this.modules.has(id);
    }

    public register(module: AppModule): void {
        if (this.modules.has(module.id)) {
            logger.warning(`[ModuleRegistry] Module ${module.id} is already registered. Overwriting...`);
        }
        this.modules.set(module.id, module);
        logger.info(`[ModuleRegistry] Module ${module.id} (${module.name}) registered successfully.`);
    }

    /**
     * Get all registered routes from all modules.
     */
    public getAllRoutes(): RouteRecordRaw[] {
        const routes: RouteRecordRaw[] = [];
        this.modules.forEach(m => {
            if (m.routes) routes.push(...m.routes);
        });
        return routes;
    }

    /**
     * Get all navigation items grouped by module.
     */
    public getNavigation(): Record<string, NavItem[]> {
        const nav: Record<string, NavItem[]> = {};
        this.modules.forEach(m => {
            if (m.navigation) nav[m.id] = m.navigation;
        });
        return nav;
    }

    /**
     * Get all registered dashboards sorted by priority.
     */
    public getDashboards(): DashboardConfig[] {
        const dashboards: DashboardConfig[] = [];
        this.modules.forEach(m => {
            if (m.dashboards) dashboards.push(...m.dashboards);
        });
        return dashboards.sort((a, b) => b.priority - a.priority);
    }

    /**
     * Run initialization for all modules.
     */
    public async initializeModules(modules: AppModule[]): Promise<void> {
        for (const module of modules) {
            if (!module.initialize) continue;
            try {
                await module.initialize();
                logger.info(`[ModuleRegistry] Module ${module.id} initialized.`);;
            } catch (error) {
                logger.error(`[ModuleRegistry] Failed to initialize module ${module.id}`, error);;
            }
        }
    }

    public async initializeAll(): Promise<void> {
        for (const module of this.modules.values()) {
            if (module.initialize) {
                try {
                    await module.initialize();
                    logger.info(`[ModuleRegistry] Module ${module.id} initialized.`);
                } catch (error) {
                    logger.error(`[ModuleRegistry] Failed to initialize module ${module.id}`, error);
                }
            }
        }
    }
}

export const registry = new ModuleRegistry();
