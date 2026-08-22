import type { RouteRecordRaw } from 'vue-router';

const mailRoutes: RouteRecordRaw[] = [
    {
        path: 'mail',
        name: 'mail',
        component: () => import('@/modules/Mail/views/mail/Index.vue'),
        meta: {
            title: 'system.mail.title',
            breadcrumb: 'system.navigation.menu.mail',
            permission: 'use mail',
            extension: 'mail',
        },
    },
];

export default mailRoutes;
