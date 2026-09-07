import type { RouteRecordRaw } from 'vue-router';

// @ts-ignore
const publicThemePage = () => import('@/modules/Layout/components/themes/PublicThemePage.vue');

const routes: RouteRecordRaw[] = [
    {
        path: 'solusi',
        name: 'sareupna-solusi',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Solusi' },
    },
    {
        path: 'pricing',
        name: 'sareupna-pricing',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Pricing' },
    },
    {
        path: 'contact',
        name: 'sareupna-contact',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Contact' },
    },
    {
        path: 'search',
        name: 'sareupna-search',
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Search' },
    },
];

export default routes;
