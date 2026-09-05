import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { handleBeforeEachGuard } from './guards';
import { publicScrollBehavior } from './publicScrollBehavior';

const publicThemePage = () => import('@/modules/Layout/components/themes/PublicThemePage.vue');

export const publicRoutes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'public-layout',
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

    let injectedThemeSlug: string | null = null;
    let activeThemeRouteRemovers: Array<() => void> = [];

    router.beforeEach(async (to, from) => {
        const rawPath = typeof window !== 'undefined' ? window.location.pathname : to.path;
        const isUnknownDirectHit = to.path === '/404' && rawPath !== '/404';

        if ((to.path !== '/404' || isUnknownDirectHit) && to.path !== '/maintenance') {
            try {
                const { useTheme } = await import('@/modules/Layout/composables/useTheme');
                const { loadActiveTheme, activeTheme } = useTheme();
                await loadActiveTheme();
                
                const currentSlug = activeTheme.value?.slug;
                if (currentSlug && currentSlug !== injectedThemeSlug) {
                    // Clean up routes from previously active theme
                    activeThemeRouteRemovers.forEach((remove) => remove());
                    activeThemeRouteRemovers = [];

                    const modules = import.meta.glob('@/modules/Layout/views/themes/*/routes.ts');
                    const loadThemeRoutes = modules[`/src/modules/Layout/views/themes/${currentSlug}/routes.ts`];
                    
                    if (loadThemeRoutes) {
                        const module = await loadThemeRoutes() as any;
                        if (module.default && Array.isArray(module.default)) {
                            module.default.forEach((route: any) => {
                                const remove = router.addRoute('public-layout', route);
                                if (typeof remove === 'function') {
                                    activeThemeRouteRemovers.push(remove);
                                }
                            });
                            injectedThemeSlug = currentSlug;
                            if (isUnknownDirectHit) {
                                return rawPath;
                            }
                            if (to.matched.length === 0 || to.name === 'public-not-found') {
                                return to.fullPath;
                            }
                        }
                    } else {
                        injectedThemeSlug = currentSlug;
                    }
                }
            } catch (e) {
                // Ignore error, theme routes just won't be loaded
            }
        }

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
