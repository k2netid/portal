import type { RouteRecordRaw } from 'vue-router';

const mediaRoutes: RouteRecordRaw[] = [
    {
        path: 'media',
        name: 'media',
        component: () => import('@/modules/Media/views/media/Index.vue'),
        meta: {
            permission: 'view media',
            extension: 'media',
            title: 'media.title',
            breadcrumb: 'sharedConsole.navigation.menu.mediaLibrary',
        },
    },
];

export default mediaRoutes;
