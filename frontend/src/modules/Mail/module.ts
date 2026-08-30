import type { AppModule } from '@/engine/types/module';
import mailRoutes from './router';
import { mailNavigation } from './navigation';

export const MailModule: AppModule = {
    id: 'mail',
    name: 'JA-Mail',
    extensionSlug: 'mail',
    routes: mailRoutes,
    navigation: mailNavigation,
};

export default MailModule;
