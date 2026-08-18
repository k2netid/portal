import type { RouteRecordRaw } from 'vue-router';

const securityRoutes: RouteRecordRaw[] = [
    {
        path: 'security-journal',
        name: 'security-journal',
        component: () => import('@/modules/Core/Security/views/Index.vue'),
        meta: {
            title: 'system.navigation.menu.securityJournal',
            breadcrumb: 'system.navigation.menu.securityJournal',
            permission: 'view security logs',
        },
    },
];

export default securityRoutes;
