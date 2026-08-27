import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { handleBeforeEachGuard } from './guards';

const publicThemePage = () => import('@/modules/Layout/components/themes/PublicThemePage.vue');

const publicRoutes: RouteRecordRaw[] = [
    {
        path: '/',
        component: () => import('@/modules/Layout/layouts/FrontendLayout.vue'),
        children: [
            {
                path: '',
                name: 'public-home',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Home' },
            },
            {
                path: 'blog',
                name: 'public-blog',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Blog' },
            },
            {
                path: 'blog/:slug',
                name: 'public-post',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Post' },
            },
            {
                path: 'about',
                name: 'public-about',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/About' },
            },
            {
                path: 'solusi',
                name: 'public-solusi',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Solusi' },
            },
            {
                path: 'services',
                name: 'public-services',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Services' },
            },
            {
                path: 'pricing',
                name: 'public-pricing',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Pricing' },
            },
            {
                path: 'career',
                name: 'public-career',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/CareerCenter' },
            },
            {
                path: 'achievement',
                name: 'public-achievement',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Achievement' },
            },
            {
                path: 'contact',
                name: 'public-contact',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Contact' },
            },
            {
                path: 'search',
                name: 'public-search',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Search' },
            },
            {
                path: 'member/login',
                name: 'public-member-login',
                component: () => import('@/modules/Member/views/Login.vue'),
                meta: { public: true, memberGuest: true },
            },
            {
                path: 'member/register',
                name: 'public-member-register',
                component: () => import('@/modules/Member/views/Register.vue'),
                meta: { public: true, memberGuest: true },
            },
            {
                path: 'member/account',
                name: 'public-member-account',
                component: () => import('@/modules/Member/views/Account.vue'),
                meta: { public: true, requiresMember: true },
            },
            {
                path: 'member/verified',
                name: 'public-member-verified',
                component: () => import('@/modules/Member/views/Verified.vue'),
                meta: { public: true },
            },
        ],
    },
    {
        path: '/404',
        name: 'public-not-found',
        component: () => import('@/modules/Core/System/views/errors/NotFound.vue'),
        meta: { public: true },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/404',
    },
];

export const createPublicRouter = () => {
    const router = createRouter({
        history: createWebHistory('/site'),
        routes: publicRoutes,
    });

    router.beforeEach(async (to, from) => {
        const result = await handleBeforeEachGuard(to, from, {
            loginPath: '/member/login',
            registerPath: '/member/register',
        });
        if (result !== undefined && result !== true) {
            return result;
        }

        const { useSystemStore } = await import('@/modules/Core/System/stores/system');
        const active = useSystemStore().activeExtensions ?? [];
        const memberRoute = to.path.startsWith('/member');
        if (memberRoute && !active.includes('member')) {
            return { path: '/404' };
        }

        if (!active.includes('member')) {
            return true;
        }

        const { useMemberStore } = await import('@/modules/Member/stores/member');
        const memberStore = useMemberStore();
        if (!memberStore.hydrated) {
            await memberStore.hydrate();
        }

        if (to.matched.some((record) => record.meta.requiresMember) && !memberStore.isAuthenticated) {
            return { path: '/member/login', query: { redirect: to.fullPath } };
        }

        if (to.matched.some((record) => record.meta.memberGuest) && memberStore.isAuthenticated) {
            return { path: '/member/account' };
        }

        return true;
    });

    return router;
};
