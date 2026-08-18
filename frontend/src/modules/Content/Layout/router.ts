import type { RouteRecordRaw } from 'vue-router';

const layoutRoutes: RouteRecordRaw[] = [
    {
        path: 'menus',
        name: 'menus',
        component: () => import('@/modules/Content/Layout/views/menus/Index.vue'),
        meta: {
            permission: 'manage menus',
            title: 'layout.menus.title',
            breadcrumb: 'layout.navigation.menu.menus',
        },
    },
    {
        path: 'widgets',
        name: 'widgets',
        component: () => import('@/modules/Content/Layout/views/widgets/Index.vue'),
        meta: {
            permission: 'manage widgets',
            title: 'layout.widgets.title',
            breadcrumb: 'layout.navigation.menu.widgets',
        },
    },
    {
        path: 'redirects',
        name: 'redirects',
        component: () => import('@/modules/Content/Layout/views/redirects/Index.vue'),
        meta: {
            permission: 'view redirects',
            title: 'layout.redirects.title',
            breadcrumb: 'layout.navigation.menu.redirects',
        },
    },
    {
        path: 'themes',
        name: 'themes',
        component: () => import('@/modules/Content/Layout/views/themes/Index.vue'),
        meta: {
            permission: 'manage themes',
            title: 'publishing.themes.title',
            breadcrumb: 'publishing.navigation.menu.themes',
        },
    },
    {
        path: 'themes/:slug/customizer',
        name: 'themes.customizer',
        component: () => import('@/modules/Content/Layout/customizer/shell/ThemeCustomizerView.vue'),
        meta: {
            permission: 'manage themes',
            title: 'publishing.theme_customizer.title',
            breadcrumb: 'publishing.theme_customizer.title',
        },
    },
    {
        path: 'site-editor',
        name: 'builder.site',
        component: () => import('@/modules/Content/Layout/views/builder/SiteEditor.vue'),
        meta: {
            permission: 'manage settings',
            title: 'Visual Site Editor',
            breadcrumb: 'Site Editor',
        },
    },
];

export default layoutRoutes;
