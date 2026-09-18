import type { RouteRecordRaw } from 'vue-router';

// @ts-ignore
const publicThemePage = () => import('@/modules/Layout/components/themes/PublicThemePage.vue');

const routes: RouteRecordRaw[] = [
    {
        path: 'programs',
        name: 'sarangenge-programs',
        alias: [
            'program',
            'program-keahlian',
            'jurusan',
            'kompetensi-keahlian',
            'solusi',
            'produk-solusi',
            'solution',
            'solutions',
        ],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Programs' },
    },
    {
        path: 'facilities',
        name: 'sarangenge-facilities',
        alias: [
            'fasilitas',
            'sarana-prasarana',
            'bengkel',
            'lab',
            'services',
            'layanan',
        ],
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
        path: 'team',
        name: 'sarangenge-team',
        alias: ['tim', 'guru', 'staf', 'guru-staf', 'direktori-guru'],
        component: publicThemePage,
        meta: { public: true, themePage: 'pages/Team' },
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
