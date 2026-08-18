import type { RouteRecordRaw } from 'vue-router';

const mediaRoutes: RouteRecordRaw[] = [
    {
        path: 'media',
        name: 'media',
        component: () => import('@/modules/Content/Media/views/media/Index.vue'),
        meta: {
            permission: 'manage media',
            title: 'media.title',
            breadcrumb: 'media.navigation.menu.media',
        },
    },
    {
        path: 'file-manager',
        name: 'file-manager',
        component: () => import('@/modules/Content/Media/views/file-manager/Index.vue'),
        meta: {
            permission: 'manage media',
            title: 'media.file_manager.title',
            breadcrumb: 'common.navigation.menu.mediaLibrary',
        },
    },
];

export default mediaRoutes;
