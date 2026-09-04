import type { RouteRecordRaw } from 'vue-router';

// @ts-ignore
const publicThemePage = () => import('@/modules/Layout/components/themes/PublicThemePage.vue');

const routes: RouteRecordRaw[] = [
    {
        path: 'solusi',
        name: 'layung-solusi',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Solusi' },
    },
    {
        path: 'services',
        name: 'layung-services',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Services' },
    },
    {
        path: 'pricing',
        name: 'layung-pricing',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Pricing' },
    },
    {
        path: 'pricing/isp',
        name: 'layung-pricing-isp',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/PricingIsp' },
    },
    {
        path: 'pricing/msp',
        name: 'layung-pricing-msp',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/PricingMsp' },
    },
    {
        path: 'career',
        name: 'layung-career',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/CareerCenter' },
    },
    {
        path: 'achievement',
        name: 'layung-achievement',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Achievement' },
    },
    {
        path: 'tim',
        name: 'layung-tim',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Tim' },
    },
];

export default routes;
