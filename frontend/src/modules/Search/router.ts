import type { RouteRecordRaw } from 'vue-router';

const searchRoutes: RouteRecordRaw[] = [
    {
        path: 'search',
        name: 'search',
        component: () => import('@/modules/Search/views/search/Hub.vue'),
        meta: {
            extension: 'search',
            title: 'search.hub.title',
            breadcrumb: 'search.navigation.menu.search',
            permission: 'manage search',
        },
    },
];

export default searchRoutes;
