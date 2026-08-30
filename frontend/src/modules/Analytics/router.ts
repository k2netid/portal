import type { RouteRecordRaw } from 'vue-router';

const analyticsRoutes: RouteRecordRaw[] = [
    {
        path: 'analytics',
        name: 'analytics',
        component: () => import('@/modules/Analytics/views/Index.vue'),
        meta: {
            extension: 'analytics',
            title: 'system.analytics.title',
            breadcrumb: 'sharedConsole.navigation.menu.analytics',
            permission: 'view analytics',
        },
    },
];

export default analyticsRoutes;
