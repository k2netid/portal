import type { AppModule } from '@/engine/types/module';
import mediaRoutes from './router';
import { mediaNavigation } from './navigation';

export const MediaModule: AppModule = {
    id: 'media',
    name: 'Media Manager',
    extensionSlug: 'media',
    routes: mediaRoutes,
    navigation: mediaNavigation,
};

export default MediaModule;
