import type { RouteRecordRaw } from 'vue-router';

// @ts-ignore
const publicThemePage = () => import('@/modules/Layout/components/themes/PublicThemePage.vue');

const routes: RouteRecordRaw[] = [
    {
        path: 'solutions',
        name: 'janari-solutions',
        alias: ['solusi'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Solutions' },
    },
    {
        path: 'pricing',
        name: 'janari-pricing',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Pricing' },
    },
    {
        path: 'career',
        name: 'janari-career',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/CareerCenter' },
    },
    {
        path: 'achievement',
        name: 'janari-achievement',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Achievement' },
    },
    {
        path: 'team',
        name: 'janari-team',
        alias: ['tim'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Team' },
    },
    {
        path: 'contact',
        name: 'janari-contact',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Contact' },
    },
];

export default routes;
