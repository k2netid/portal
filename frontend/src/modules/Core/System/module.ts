import { defineAsyncComponent } from 'vue';
import type { AppModule } from '@/engine/types/module';
import systemRoutes from './router/index';
import { systemNavigation } from './navigation';

export const SystemModule: AppModule = {
    id: 'system',
    name: 'System Platform',
    routes: systemRoutes,
    navigation: systemNavigation,
    dashboards: [
        {
            id: 'system-admin',
            priority: 100,
            routeName: 'system.dashboard',
            condition: (user) => user?.roles?.some((r: any) => r.name === 'super') ?? false,
            component: defineAsyncComponent(() => import('./components/dashboard/ConsoleDashboard.vue'))
        }
    ]
};

export default SystemModule;
