import type { AppModule } from '@/engine/types/module';
import analyticsRoutes from './router';
import { analyticsNavigation } from './navigation';

export const AnalyticsModule: AppModule = {
    id: 'analytics',
    name: 'Traffic & Analytics',
    routes: analyticsRoutes,
    navigation: analyticsNavigation,
};

export default AnalyticsModule;
