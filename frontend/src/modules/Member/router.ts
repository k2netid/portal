import type { RouteRecordRaw } from 'vue-router';

const memberRoutes: RouteRecordRaw[] = [
    {
        path: 'members',
        name: 'members.index',
        component: () => import('@/modules/Member/views/members/Index.vue'),
        meta: {
            permission: 'view members',
            extension: 'member',
            title: 'member.title',
            breadcrumb: 'member.navigation.menu.members',
        },
    },
];

export default memberRoutes;
