import type { RouteRecordRaw } from 'vue-router';

const libraryRoutes: RouteRecordRaw[] = [
    {
        path: 'tags',
        name: 'tags',
        component: () => import('@/modules/Library/views/tags/Index.vue'),
        meta: {
            permission: 'manage content',
            extension: 'library',
            title: 'library.tags.title',
            breadcrumb: 'library.navigation.menu.tags',
        },
    },
    {
        path: 'custom-fields',
        name: 'custom-fields',
        component: () => import('@/modules/Library/views/custom-fields/Index.vue'),
        meta: {
            permission: 'manage content',
            extension: 'library',
            title: 'library.customFields.title',
            breadcrumb: 'library.navigation.menu.customFields',
        },
    },
];

export default libraryRoutes;
