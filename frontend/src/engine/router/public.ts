import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { handleBeforeEachGuard } from './guards';
import { publicScrollBehavior } from './publicScrollBehavior';

const publicThemePage = () => import('@/modules/Layout/components/themes/PublicThemePage.vue');

export const publicRoutes: RouteRecordRaw[] = [
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
                // Janari has Solusi (not Services); avoid falling through to Sarangenge Services.vue
                meta: { public: true, themePage: 'pages/Solusi' },
            },
            {
                path: 'pricing',
                name: 'public-pricing',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Pricing' },
            },
            {
                path: 'pricing/isp',
                name: 'public-pricing-isp',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/PricingIsp' },
            },
            {
                path: 'pricing/msp',
                name: 'public-pricing-msp',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/PricingMsp' },
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
                path: 'tim',
                name: 'public-tim',
                component: publicThemePage,
                meta: { public: true, themePage: 'pages/Tim' },
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
                path: 'member/forgot-password',
                name: 'public-member-forgot-password',
                component: () => import('@/modules/Member/views/ForgotPassword.vue'),
                meta: { public: true, memberGuest: true },
            },
            {
                path: 'member/reset-password',
                name: 'public-member-reset-password',
                component: () => import('@/modules/Member/views/ResetPassword.vue'),
                meta: { public: true, memberGuest: true },
            },
            {
                path: 'member/email-changed',
                name: 'public-member-email-changed',
                component: () => import('@/modules/Member/views/EmailChanged.vue'),
                meta: { public: true },
            },
            {
                path: 'member',
                name: 'member-portal',
                component: () => import('@/modules/Member/layouts/MemberPortalLayout.vue'),
                meta: { public: true, requiresMember: true, memberShell: true },
                redirect: { name: 'member.dashboard' },
                children: [
                    {
                        path: '',
                        name: 'member.dashboard',
                        component: () => import('@/modules/Member/views/Dashboard.vue'),
                        meta: { public: true, requiresMember: true, memberShell: true },
                    },
                    {
                        path: 'profile',
                        name: 'member.profile',
                        component: () => import('@/modules/Member/views/Profile.vue'),
                        meta: { public: true, requiresMember: true, memberShell: true },
                    },
                    {
                        path: 'security',
                        name: 'member.security',
                        component: () => import('@/modules/Member/views/Security.vue'),
                        meta: { public: true, requiresMember: true, memberShell: true },
                    },
                    {
                        path: 'unavailable',
                        name: 'member.feature-unavailable',
                        component: () => import('@/modules/Member/views/FeatureUnavailable.vue'),
                        meta: { public: true, requiresMember: true, memberShell: true },
                    },
                ],
            },
            {
                path: 'member/account',
                redirect: { name: 'member.profile' },
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
        history: createWebHistory('/'),
        routes: publicRoutes,
        scrollBehavior: publicScrollBehavior,
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
            return { name: 'member.dashboard' };
        }

        const extensionSlug = to.matched.map((record) => record.meta.memberExtension).find(Boolean);
        if (typeof extensionSlug === 'string' && !active.includes(extensionSlug)) {
            return { name: 'member.feature-unavailable' };
        }

        const capability = to.matched.map((record) => record.meta.memberCapability).find(Boolean);
        if (typeof capability === 'string') {
            if (!memberStore.portal) {
                await memberStore.fetchPortal();
            }
            if (!memberStore.hasCapability(capability)) {
                return { name: 'member.feature-unavailable' };
            }
        }

        if (
            to.matched.some((record) => record.meta.requiresVerified)
            && memberStore.member?.email_verified !== true
        ) {
            return { name: 'member.dashboard' };
        }

        return true;
    });

    return router;
};
