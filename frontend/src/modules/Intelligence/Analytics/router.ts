import type { RouteRecordRaw } from 'vue-router';

const analyticsRoutes: RouteRecordRaw[] = [
    {
        path: 'analytics',
        name: 'analytics',
        component: () => import('@/modules/Intelligence/Analytics/views/Index.vue'),
        meta: {
            title: 'system.analytics.title',
            breadcrumb: 'search.navigation.menu.analytics',
            permission: 'manage settings',
        },
    },
];

export default analyticsRoutes;
