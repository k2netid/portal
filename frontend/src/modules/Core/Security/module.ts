import type { AppModule } from '@/engine/types/module';
import securityRoutes from './router';
import { securityNavigation } from './navigation';

export const SecurityModule: AppModule = {
    id: 'security',
    name: 'Security & Access Control',
    routes: securityRoutes,
    navigation: securityNavigation,
};

export default SecurityModule;
