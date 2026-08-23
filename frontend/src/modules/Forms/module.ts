import type { AppModule } from '@/engine/types/module';
import formsRoutes from './router';
import { formsNavigation } from './navigation';

export const FormsModule: AppModule = {
    id: 'forms',
    name: 'Forms',
    extensionSlug: 'forms',
    routes: formsRoutes,
    navigation: formsNavigation,
};

export default FormsModule;
