import type { RouteRecordRaw } from 'vue-router';

const mailRoutes: RouteRecordRaw[] = [
    {
        path: 'mail',
        name: 'mail',
        component: () => import('@/modules/Mail/views/mail/Index.vue'),
        meta: {
            title: 'mail.title',
            breadcrumb: 'mail.navigationMenuMail',
            permission: 'use mail',
            extension: 'mail',
        },
    },
];

export default mailRoutes;
