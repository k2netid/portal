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
    {
        path: 'members/create',
        name: 'members.create',
        component: () => import('@/modules/Member/views/members/Create.vue'),
        meta: {
            permission: 'manage members',
            extension: 'member',
            title: 'member.form.createTitle',
            breadcrumb: 'member.form.createTitle',
            noCache: true,
        },
    },
    {
        path: 'members/:id/edit',
        name: 'members.edit',
        component: () => import('@/modules/Member/views/members/Edit.vue'),
        meta: {
            permission: 'manage members',
            extension: 'member',
            title: 'member.form.editTitle',
            breadcrumb: 'member.form.editTitle',
            noCache: true,
        },
    },
    {
        path: 'members/:id',
        name: 'members.show',
        component: () => import('@/modules/Member/views/members/Show.vue'),
        meta: {
            permission: 'view members',
            extension: 'member',
            title: 'member.detail.title',
            breadcrumb: 'member.detail.title',
        },
    },
];

export default memberRoutes;
