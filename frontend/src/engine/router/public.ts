import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { handleBeforeEachGuard } from './guards';
import { SECURITY_ROUTES } from '@/config/security';

const publicRoutes: RouteRecordRaw[] = [
    {
        path: '/',
        component: () => import('@/modules/Layout/layouts/FrontendLayout.vue'),
        children: [
            {
                path: '',
                name: 'public-home',
                component: () => import('@/modules/Layout/views/themes/zenith/pages/Home.vue'),
                meta: { public: true },
            },
            {
                path: 'blog',
                name: 'public-blog',
                component: () => import('@/modules/Layout/views/themes/zenith/pages/Blog.vue'),
                meta: { public: true },
            },
            {
                path: 'blog/:slug',
                name: 'public-post',
                component: () => import('@/modules/Layout/views/themes/zenith/pages/Post.vue'),
                meta: { public: true },
            },
            {
                path: 'about',
                name: 'public-about',
                component: () => import('@/modules/Layout/views/themes/zenith/pages/About.vue'),
                meta: { public: true },
            },
            {
                path: 'contact',
                name: 'public-contact',
                component: () => import('@/modules/Layout/views/themes/zenith/pages/Contact.vue'),
                meta: { public: true },
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
