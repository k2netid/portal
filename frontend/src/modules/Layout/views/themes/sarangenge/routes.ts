import type { RouteRecordRaw } from 'vue-router';

// @ts-ignore
const publicThemePage = () => import('@/modules/Layout/components/themes/PublicThemePage.vue');

const routes: RouteRecordRaw[] = [
    {
        path: 'solusi',
        name: 'sarangenge-solusi',
        alias: ['produk-solusi', 'solution', 'solutions'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Solusi' },
    },
    {
        path: 'services',
        name: 'sarangenge-services',
        alias: ['layanan'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Services' },
    },
    {
        path: 'programs',
        name: 'sarangenge-programs',
        alias: ['program', 'program-keahlian', 'jurusan', 'kompetensi-keahlian'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Programs' },
    },
    {
        path: 'facilities',
        name: 'sarangenge-facilities',
        alias: ['fasilitas', 'sarana-prasarana', 'bengkel', 'lab'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Facilities' },
    },
    {
        path: 'pricing',
        name: 'sarangenge-pricing',
        alias: ['biaya', 'paket', 'ppdb-biaya'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Pricing' },
    },
    {
        path: 'career',
        name: 'sarangenge-career',
        alias: ['karir', 'bkk', 'bursa-kerja', 'alumni', 'pusat-karier'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/CareerCenter' },
    },
    {
        path: 'achievement',
        name: 'sarangenge-achievement',
        alias: ['prestasi', 'penghargaan', 'sorotan-prestasi'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Achievement' },
    },
    {
        path: 'tim',
        name: 'sarangenge-tim',
        alias: ['guru', 'staf', 'guru-staf', 'direktori-guru', 'team'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Tim' },
    },
    {
        path: 'contact',
        name: 'sarangenge-contact',
        alias: ['kontak', 'hubungi-kami'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Contact' },
    },
];

export default routes;
