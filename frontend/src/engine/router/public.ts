import { logger } from '@/shared/utils/logger';
import type { RouteRecordRaw } from 'vue-router';
import { createRouter, createWebHistory } from 'vue-router';
import frontendRoutes from './frontend';
import { SECURITY_ROUTES } from '@/config/security';
import { handleBeforeEachGuard } from './guards';
import { trackLastSafeRoute } from '@/shared/utils/errorReturn';
import { trackRouteVisit } from './analytics-tracker';
import { attemptChunkRecoveryReload, isChunkLoadError } from '@/shared/utils/chunkRecovery';
import { useSystemError } from '@/shared/composables/useSystemError';
import i18n from '@/engine/i18n';

const routes: Array<RouteRecordRaw> = [
    ...frontendRoutes,
    {
        path: SECURITY_ROUTES.login,
        name: 'login',
        component: () => import('@/modules/Core/System/views/auth/Login.vue'),
        meta: { guestOnly: true, authContext: 'system' },
    },
    {
        path: SECURITY_ROUTES.register,
        name: 'register',
        component: () => import('@/modules/Core/System/views/auth/Register.vue'),
        meta: { guestOnly: true, authContext: 'system' },
    },
    {
        path: '/member/login',
        name: 'member-login',
        component: () => import('@/modules/Core/System/views/auth/Login.vue'),
        meta: { guestOnly: true, authContext: 'member' },
    },
    {
        path: '/member/register',
        name: 'member-register',
        component: () => import('@/modules/Core/System/views/auth/Register.vue'),
        meta: { guestOnly: true, authContext: 'member' },
    },
    {
        path: '/member',
        component: () => import('@/modules/Core/System/layouts/MemberLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                redirect: { name: 'member-profile' },
            },
            {
                path: 'profile',
                name: 'member-profile',
                component: () => import('@/modules/Core/System/views/member/MemberProfileView.vue'),
                meta: {
                    requiresAuth: true,
                    title: 'system.member.nav.profile',
                },
            },
            {
                path: 'bookmarks',
                name: 'member-bookmarks',
                component: () => import('@/modules/Core/System/views/member/MemberBookmarksView.vue'),
                meta: {
                    requiresAuth: true,
                    title: 'system.member.nav.bookmarks',
                },
            },
            {
                path: 'comments',
                name: 'member-comments',
                component: () => import('@/modules/Core/System/views/member/MemberCommentsView.vue'),
                meta: {
                    requiresAuth: true,
                    title: 'system.member.nav.comments',
                },
            },
            {
                path: 'newsletter',
                name: 'member-newsletter',
                component: () => import('@/modules/Core/System/views/member/MemberNewsletterView.vue'),
                meta: {
                    requiresAuth: true,
                    title: 'system.member.nav.newsletter',
                },
            },
        ],
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
        path: '/setup',
        name: 'setup',
        component: () => import('@/modules/Core/System/views/SetupView.vue'),
        meta: { public: true, title: 'system.setup.title' },
    },
    {
        path: '/403',
        name: 'forbidden',
        component: () => import('@/modules/Core/System/views/errors/Forbidden.vue'),
        meta: { public: true },
    },
    {
        path: '/500',
        name: 'server-error',
        component: () => import('@/modules/Core/System/views/errors/ServerError.vue'),
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
        path: '/429',
        name: 'too-many-requests',
        component: () => import('@/modules/Core/System/views/errors/RateLimit.vue'),
        meta: { public: true },
    },
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
        if (to.hash) {
            return { el: to.hash, top: 80, behavior: 'smooth' };
        }
        return { top: 0, left: 0, behavior: 'auto' };
    },
});

// Keep existing behavior for maintenance checks and probe path hardening.
router.beforeEach(async (to, from) => {
    return handleBeforeEachGuard(to, from, {
        loginPath: '/member/login',
        registerPath: '/member/register',
    });
});

router.afterEach((to) => {
    trackLastSafeRoute(to.fullPath, 'public', true);
});

let isHandlingRouterError = false;
router.onError((error) => {
    if (isHandlingRouterError) return;
    if (isChunkLoadError(error) && attemptChunkRecoveryReload()) return;
    isHandlingRouterError = true;
    logger.error('Public router error:', error);
    const { showError } = useSystemError();
    const t = i18n.global.t;
    showError({
        code: 500,
        title: String(t('system.routes.appError')),
        message: error.message || String(t('system.routes.navigationError')),
        description: String(t('common.messages.router.navigationError')),
        reason: 'Router Navigation Error',
        redirect: '/',
    });
    setTimeout(() => { isHandlingRouterError = false; }, 1000);
});

router.afterEach((to, from) => {
    trackRouteVisit(to, from);
});

export default router;
