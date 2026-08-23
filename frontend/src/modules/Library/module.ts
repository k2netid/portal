import type { AppModule } from '@/engine/types/module';
import libraryRoutes from './router';
import { libraryNavigation } from './navigation';

export const LibraryModule: AppModule = {
  id: 'library',
  name: 'Library',
  extensionSlug: 'library',
  routes: libraryRoutes,
  navigation: libraryNavigation,
};

export default LibraryModule;
