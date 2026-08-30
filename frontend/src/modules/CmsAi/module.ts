import type { AppModule } from '@/engine/types/module';
import cmsAiRoutes from './router';
import { cmsAiNavigation } from './navigation';

export const CmsAiModule: AppModule = {
    id: 'cms-ai',
    name: 'CMS AI',
    extensionSlug: 'cms-ai',
    routes: cmsAiRoutes,
    navigation: cmsAiNavigation,
};

export default CmsAiModule;
