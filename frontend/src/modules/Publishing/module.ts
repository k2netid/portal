import { defineAsyncComponent } from 'vue';
import type { AppModule } from '@/engine/types/module';
import publishingRoutes from './router';
import { publishingNavigation } from './navigation';

export const PublishingModule: AppModule = {
    id: 'publishing',
    name: 'Content Management',
    extensionSlug: 'publishing',
    routes: publishingRoutes,
    navigation: publishingNavigation,
    dashboards: [
        {
            id: 'publishing-creator',
            priority: 40,
            routeName: 'contents.index',
            condition: (_user, auth) => auth.hasPermission('create content') || auth.hasPermission('edit content') || auth.hasPermission('upload media'),
            component: defineAsyncComponent(() => import('./components/dashboard/CreatorDashboard.vue'))
        },
        {
            id: 'publishing-viewer',
            priority: 0,
            routeName: 'contents',
            condition: (_user, auth) => auth.hasPermission('manage content'),
            component: defineAsyncComponent(() => import('./components/dashboard/ViewerDashboard.vue'))
        }
    ]
};

export default PublishingModule;
