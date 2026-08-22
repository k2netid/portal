import { logger } from '@/shared/utils/logger';
import type { RouteRecordRaw } from 'vue-router';
import { createRouter, createWebHistory } from 'vue-router';
import { SECURITY_ROUTES, isLegitimateConsoleSlugPath, shouldGuestReceiveSecurityNotFound } from '@/config/security';
import { handleBeforeEachGuard } from './guards';
import { trackLastSafeRoute } from '@/shared/utils/errorReturn';
import { prefetchConsoleHubRoutes } from '@/shared/utils/routePrefetch';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { attemptChunkRecoveryReload, isChunkLoadError } from '@/shared/utils/chunkRecovery';
import { useSystemError } from '@/shared/composables/useSystemError';
import { registry } from '@/engine/registry';
import { useConsoleContextStore } from '@/engine/stores/consoleContext';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import i18n from '@/engine/i18n';

const adminPath = SECURITY_ROUTES.dashboardBase;
const loginPath = SECURITY_ROUTES.login;
const registerPath = SECURITY_ROUTES.register;

const baseRoutes: Array<RouteRecordRaw> = [
    {
        path: '/',
        name: 'root',
        redirect: () => {
            const authStore = useAuthStore();
            if (!authStore.isAuthenticated) {
                return { path: SECURITY_ROUTES.login };
            }
            const systemStore = useSystemStore();
            const dashboardSlug = String(systemStore.consoleDashboardSlug || 'dash');
            return { path: `/${dashboardSlug}/dashboard` };
        },
    },
    {
        path: '/maintenance',
        name: 'maintenance',
        component: () => import('@/shared/views/Maintenance.vue'),
        meta: { public: true, title: 'system.routes.maintenance' },
    },
    {
        path: '/install',
        name: 'install',
        component: () => import('@/modules/Core/System/views/InstallView.vue'),
        meta: { public: true, title: 'system.installer.title' },
    },
    {
        path: SECURITY_ROUTES.login,
        name: 'login',
        component: () => import('@/modules/Core/System/views/auth/Login.vue'),
        meta: { guestOnly: true, authContext: 'system' },
    },
    {
        path: '/login',
        name: 'login-alias',
        component: () => import('@/modules/Core/System/views/auth/Login.vue'),
        meta: { guestOnly: true, authContext: 'system' },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/modules/Core/System/views/auth/Register.vue'),
        meta: { guestOnly: true, authContext: 'system' },
    },
    {
        path: '/auth/console-sign-up',
        name: 'register-alias',
        component: () => import('@/modules/Core/System/views/auth/Register.vue'),
        meta: { guestOnly: true, authContext: 'system' },
    },
    {
        path: '/public/system/auth/forgot-password',
        name: 'forgot-password',
        component: () => import('@/modules/Core/System/views/auth/ForgotPassword.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/public/system/auth/reset-password',
        name: 'reset-password',
        component: () => import('@/modules/Core/System/views/auth/ResetPassword.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/403',
        name: 'forbidden',
        component: () => import('@/modules/Core/System/views/errors/Forbidden.vue'),
        meta: { public: true },
    },
    {
        path: '/404',
        name: 'not-found',
        component: () => import('@/modules/Core/System/views/errors/NotFound.vue'),
        meta: { public: true },
    },
    {
        path: '/419',
        name: 'session-expired',
        component: () => import('@/modules/Core/System/views/errors/SessionExpired.vue'),
        meta: { public: true },
    },
    {
        path: '/500',
        name: 'server-error',
        component: () => import('@/modules/Core/System/views/errors/ServerError.vue'),
        meta: { public: true },
    },
];

const dashboardRoute: RouteRecordRaw = {
    path: '/:dashboard_slug?',
    component: () => import('@/modules/Core/System/layouts/ConsoleLayout.vue'),
    meta: { auth: true },
    children: [
        {
            path: 'dashboard',
            name: 'system.dashboard',
            component: () => import('@/modules/Core/System/views/Dashboard.vue'),
            meta: { permission: 'view dashboard' },
        },
        {
            path: '',
            name: 'dashboard',
            redirect: (to) => {
                const authStore = useAuthStore();

                if (!authStore.isAuthenticated) {
                    // Prefer SPA 404 over leaking the console login URL for scanners
                    // and for guests hitting /dash (see auth-probe-notfound e2e).
                    if (shouldGuestReceiveSecurityNotFound(to.path)) {
                        return { name: 'not-found' };
                    }
                    const slug = String(to.params.dashboard_slug ?? '').trim();
                    if (slug !== '' && !isLegitimateConsoleSlugPath(`/${slug}`)) {
                        return { name: 'not-found' };
                    }
                    return { name: 'login' };
                }

                const consoleStore = useConsoleContextStore();
                const systemStore = useSystemStore();
                const dashboardSlug = String(to.params.dashboard_slug || systemStore.consoleDashboardSlug || 'dash');

                const isSuper = authStore.getRoleRank() >= 100;

                logger.info('[Router:Console] Resolving landing', {
                    isSuper,
                    context: consoleStore.context,
                });

                if (isSuper && consoleStore.isSystem) {
                    return { path: `/${dashboardSlug}/dashboard` };
                }

                const dashboards = registry.getDashboards();
                const availableDashboard = dashboards.find((d) => (
                    d.condition ? d.condition(authStore.user, authStore) : true
                ));

                if (availableDashboard) {
                    return {
                        name: availableDashboard.routeName || 'system.dashboard',
                    };
                }

                if (isSuper) {
                    return { path: `/${dashboardSlug}/dashboard` };
                }

                return { path: `/${dashboardSlug}/dashboard` };
            },
        },
        ...registry.getAllRoutes().filter((r) => r.name !== 'system.dashboard'),
    ],
};

export function createConsoleRouter() {
    const routes: Array<RouteRecordRaw> = [
        ...baseRoutes,
        dashboardRoute,
        {
            path: '/:pathMatch(.*)*',
            name: 'catch-all',
            redirect: { name: 'not-found' },
        },
    ];

    const router = createRouter({
        history: createWebHistory(),
        routes,
        scrollBehavior(to, _from, savedPosition) {
            if (savedPosition) return savedPosition;
            if (to.hash) return { el: to.hash, top: 80, behavior: 'smooth' };
            return { top: 0, left: 0, behavior: 'auto' };
        },
    });

    router.beforeEach(async (to, from) => {
        return handleBeforeEachGuard(to, from, {
            loginPath,
            registerPath,
            adminPath,
        });
    });

    router.afterEach((to) => {
        const authStore = useAuthStore();
        trackLastSafeRoute(to.fullPath, 'console', authStore.isAuthenticated);
    });

    let hubRoutesPrefetched = false;
    router.afterEach((to) => {
        if (hubRoutesPrefetched || !useAuthStore().isAuthenticated) {
            return;
        }
        if (to.matched.some((r) => r.meta?.requiresAuth || r.meta?.auth)) {
            hubRoutesPrefetched = true;
            prefetchConsoleHubRoutes(router);
        }
    });

    let isHandlingRouterError = false;
    router.onError((error) => {
        if (isHandlingRouterError) return;
        if (isChunkLoadError(error) && attemptChunkRecoveryReload()) return;
        isHandlingRouterError = true;
        logger.error('Console router error:', error);
        const { showError } = useSystemError();
        const t = i18n.global.t;
        showError({
            code: 500,
            title: String(t('system.routes.dashboardError')),
            message: error.message || String(t('system.routes.dashboardNavigationError')),
            description: String(t('common.messages.router.dashboardNavigationError')),
            reason: 'Router Navigation Error',
            redirect: adminPath,
        });
        setTimeout(() => { isHandlingRouterError = false; }, 1000);
    });

    return router;
}
