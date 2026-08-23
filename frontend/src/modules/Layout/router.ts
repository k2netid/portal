import type { RouteRecordRaw } from 'vue-router';

const layoutRoutes: RouteRecordRaw[] = [
    {
        path: 'menus',
        name: 'menus',
        component: () => import('@/modules/Layout/views/menus/Index.vue'),
        meta: {
            permission: 'manage menus',
            extension: 'layout',
            title: 'layout.menus.title',
            breadcrumb: 'layout.navigation.menu.menus',
        },
    },
    {
        path: 'widgets',
        name: 'widgets',
        component: () => import('@/modules/Layout/views/widgets/Index.vue'),
        meta: {
            permission: 'manage widgets',
            extension: 'layout',
            title: 'layout.widgets.title',
            breadcrumb: 'layout.navigation.menu.widgets',
        },
    },
    {
        path: 'redirects',
        name: 'redirects',
        component: () => import('@/modules/Layout/views/redirects/Index.vue'),
        meta: {
            permission: 'view redirects',
            extension: 'layout',
            title: 'layout.redirects.title',
            breadcrumb: 'layout.navigation.menu.redirects',
        },
    },
];

export default layoutRoutes;
