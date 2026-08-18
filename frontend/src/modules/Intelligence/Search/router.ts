import type { RouteRecordRaw } from 'vue-router';

const searchRoutes: RouteRecordRaw[] = [
    {
        path: 'search',
        name: 'search',
        component: () => import('@/modules/Intelligence/Search/views/search/Hub.vue'),
        meta: {
            title: 'search.hub.title',
            breadcrumb: 'search.navigation.menu.search',
            permission: 'manage search',
        },
    },
];

export default searchRoutes;
