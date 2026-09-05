import type { RouteRecordRaw } from 'vue-router';

// @ts-ignore
const publicThemePage = () => import('@/modules/Layout/components/themes/PublicThemePage.vue');

const routes: RouteRecordRaw[] = [
    {
        path: 'solusi',
        name: 'janari-solusi',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Solusi' },
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
        path: 'tim',
        name: 'janari-tim',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Tim' },
    },
    {
        path: 'contact',
        name: 'janari-contact',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Contact' },
    },
];

export default routes;
