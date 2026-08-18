import type { AppModule } from '@/engine/types/module';
import searchRoutes from './router';
import searchNavigation from './navigation';

export const SearchModule: AppModule = {
    id: 'search',
    name: 'Search & Indexing',
    routes: searchRoutes,
    navigation: searchNavigation,
};

export default SearchModule;
