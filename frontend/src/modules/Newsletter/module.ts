import type { AppModule } from '@/engine/types/module';
import newsletterRoutes from './router';
import { newsletterNavigation } from './navigation';

export const NewsletterModule: AppModule = {
    id: 'newsletter',
    name: 'Newsletter',
    extensionSlug: 'newsletter',
    routes: newsletterRoutes,
    navigation: newsletterNavigation,
};

export default NewsletterModule;
