import type { RouteRecordRaw } from 'vue-router';

const libraryRoutes: RouteRecordRaw[] = [
    {
        path: 'tags',
        name: 'tags',
        component: () => import('@/modules/Content/Library/views/tags/Index.vue'),
        meta: {
            permission: 'manage content',
            title: 'library.tags.title',
            breadcrumb: 'library.navigation.menu.tags',
        },
    },
    {
        path: 'custom-fields',
        name: 'custom-fields',
        component: () => import('@/modules/Content/Library/views/custom-fields/Index.vue'),
        meta: {
            permission: 'manage content',
            title: 'library.customFields.title',
            breadcrumb: 'library.navigation.menu.customFields',
        },
    },
];

export default libraryRoutes;
