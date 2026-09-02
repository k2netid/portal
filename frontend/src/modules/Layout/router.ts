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
    {
        path: 'themes',
        name: 'themes',
        component: () => import('@/modules/Layout/views/themes/Index.vue'),
        meta: {
            permission: 'manage themes',
            extension: 'layout',
            title: 'publishing.themes.title',
            breadcrumb: 'publishing.navigation.menu.themes',
        },
    },
    {
        path: 'themes/:slug/customizer',
        name: 'themes.customizer',
        component: () => import('@/modules/Layout/customizer/shell/ThemeCustomizerView.vue'),
        meta: {
            permission: 'manage themes',
            extension: 'layout',
            title: 'publishing.theme_customizer.title',
            breadcrumb: 'publishing.theme_customizer.title',
            fullWidth: true,
        },
    },
    {
        path: 'site-editor',
        name: 'builder.site',
        component: () => import('@/modules/Layout/views/builder/SiteEditor.vue'),
        meta: {
            permission: 'edit content',
            extension: 'layout',
            title: 'Visual Site Editor',
            breadcrumb: 'layout.navigation.menu.siteEditor',
            // Fresh mount each visit — avoids KeepAlive stale builder methods/locks/content.
            noCache: true,
        },
    },
];

export default layoutRoutes;
