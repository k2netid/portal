import type { AppModule } from '@/engine/types/module';
import formsRoutes from '../Forms/router';
import formsNavigation from '../Forms/navigation';
import layoutRoutes from '../Layout/router';
import layoutNavigation from '../Layout/navigation';
import libraryRoutes from '../Library/router';
import libraryNavigation from '../Library/navigation';

/**
 * Consolidated console module for Layout, Forms, and Library (R2026.15).
 * Route names and API paths are unchanged; only registry registration is unified.
 */
export const ContentStudioModule: AppModule = {
    id: 'content-studio',
    name: 'Content Studio',
    routes: [...layoutRoutes, ...formsRoutes, ...libraryRoutes],
    navigation: [...layoutNavigation, ...formsNavigation, ...libraryNavigation],
};

export default ContentStudioModule;
