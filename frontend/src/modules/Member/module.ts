import type { AppModule } from '@/engine/types/module';
import memberRoutes from './router';
import { memberNavigation } from './navigation';

export const MemberModule: AppModule = {
    id: 'member',
    name: 'Members',
    extensionSlug: 'member',
    routes: memberRoutes,
    navigation: memberNavigation,
};

export default MemberModule;
