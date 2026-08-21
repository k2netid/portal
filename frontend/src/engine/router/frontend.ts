import type { RouteRecordRaw } from 'vue-router';

// Frontend base routes for Core Engine
const frontendRoutes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'frontend',
        redirect: '/dash',
    },
    {
        path: '/terms',
        name: 'terms',
        component: () => import('@/modules/Core/System/views/legal/Terms.vue'),
        meta: {
            title: 'system.legal.terms',
        },
    },
    {
        path: '/privacy',
        name: 'privacy',
        component: () => import('@/modules/Core/System/views/legal/Privacy.vue'),
        meta: {
            title: 'system.legal.privacy',
        },
    },
];

export default frontendRoutes;
