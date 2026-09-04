import type { RouteRecordRaw } from 'vue-router';

// @ts-ignore
const publicThemePage = () => import('@/modules/Layout/components/themes/PublicThemePage.vue');

const routes: RouteRecordRaw[] = [
    {
        path: 'solusi',
        name: 'sarangenge-solusi',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Solusi' },
    },
    {
        path: 'services',
        name: 'sarangenge-services',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Services' },
    },
    {
        path: 'programs',
        name: 'sarangenge-programs',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Programs' },
    },
    {
        path: 'facilities',
        name: 'sarangenge-facilities',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Facilities' },
    },
    {
        path: 'pricing',
        name: 'sarangenge-pricing',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Pricing' },
    },
    {
        path: 'career',
        name: 'sarangenge-career',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/CareerCenter' },
    },
    {
        path: 'achievement',
        name: 'sarangenge-achievement',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Achievement' },
    },
    {
        path: 'tim',
        name: 'sarangenge-tim',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Tim' },
    },
];

export default routes;
