import type { AppModule } from '@/engine/types/module';
import layoutRoutes from './router';
import { layoutNavigation } from './navigation';

export const LayoutModule: AppModule = {
    id: 'layout',
    name: 'Layout',
    extensionSlug: 'layout',
    routes: layoutRoutes,
    navigation: layoutNavigation,
};

export default LayoutModule;
