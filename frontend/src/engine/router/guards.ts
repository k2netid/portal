import { logger } from '@/shared/utils/logger';
import type { RouteLocationNormalized, RouteLocationRaw } from 'vue-router';
import { preloadLocalesForRoute } from '@/engine/i18n/deferredLocales';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import {
    isReservedPublicContentSlug,
    shouldBlockOnPublicSite,
    shouldGuestReceiveSecurityNotFound,
} from '@/config/security';
import { errorReturnShell, rememberRouteBeforeError } from '@/shared/utils/errorReturn';
import { isPublicShell } from '@/config/shell';
import { resetLockdown } from '@/engine/api/client';
import { resolveIsConsoleEntrypoint } from '@/engine/router/entrypoint';

interface GuardPaths {
    loginPath: string;
    registerPath: string;
    adminPath?: string;
}

export const handleBeforeEachGuard = async (
    to: RouteLocationNormalized,
    from: RouteLocationNormalized,
    paths: GuardPaths,
): Promise<RouteLocationRaw | boolean | void> => {
    // Ensure all dynamic locales for this route are fully loaded before rendering
    try {
        await preloadLocalesForRoute(to.path, to.name);
    } catch (e) {
        logger.error('Failed to preload locales in router guard:', e);
    }

    const authStore = useAuthStore();
    const systemStore = useSystemStore();
    const onPublicSite = isPublicShell();
    const returnShell = errorReturnShell();

    // Public shell encountering console/auth entrypoint: full browser navigation to console.html
    if (onPublicSite && resolveIsConsoleEntrypoint(to.path)) {
        if (typeof window !== 'undefined') {
            window.location.assign(to.fullPath);
            return false;
        }
    }

    // Public shell: probe/console/auth paths → 404 (hub has no CMS routes).
    if (onPublicSite && shouldBlockOnPublicSite(to.path)) {
        rememberRouteBeforeError(from.fullPath, returnShell, authStore.isAuthenticated);
        return { path: '/404', replace: true };
    }

    if (onPublicSite && to.name === 'page') {
        const slug = String(to.params.slug ?? '');
        if (isReservedPublicContentSlug(slug)) {
            rememberRouteBeforeError(from.fullPath, returnShell, authStore.isAuthenticated);
            return { path: '/404', replace: true };
        }
    }

    // Console shell: after bootstrap, unauthenticated users go to login (not SPA 404).
    if (
        !onPublicSite
        && authStore.authBootstrapComplete
        && !authStore.isAuthenticated
        && to.matched.some((record) => record.meta.requiresAuth || record.meta.auth)
    ) {
        return {
            path: paths.loginPath,
            query: { redirect: to.fullPath },
            replace: true,
        };
    }

    // Probe/scanner paths on console host still sinkhole to 404.
    if (
        !onPublicSite
        && authStore.authBootstrapComplete
        && !authStore.isAuthenticated
        && shouldGuestReceiveSecurityNotFound(to.path)
    ) {
        rememberRouteBeforeError(from.fullPath, returnShell, authStore.isAuthenticated);
        return { path: '/404', replace: true };
    }

    // 0. Preload Settings Instantly to Resolve Dynamic Dashboard Slug
    const isSpecialRoute =
        to.name === 'maintenance' ||
        to.name === 'login' ||
        to.name === 'register' ||
        to.name === 'forgot-password' ||
        to.name === 'reset-password' ||
        to.name === 'verify-email' ||
        to.name === 'session-expired' ||
        to.name === 'member-login' ||
        to.name === 'member-register' ||
        to.name === 'member-profile' ||
        to.name === 'member-bookmarks' ||
        to.name === 'member-comments' ||
        to.name === 'member-newsletter' ||
        to.path === '/maintenance' ||
        to.path === paths.loginPath ||
        to.path === paths.registerPath;

    if (!systemStore.publicSettingsLoaded && !isSpecialRoute) {
        try {
            if (!systemStore.publicSettingsPromise) {
                await systemStore.fetchPublicSettings();
            } else {
                await systemStore.publicSettingsPromise;
            }
        } catch (e) {
            logger.error('Failed to preload settings in router guard:', e);
        }
    }

    // Ensure console URLs always carry the current dashboard slug.
    // Without this, some named-route navigations can produce slug-less URLs
    // (e.g. `/settings/...`) which refresh to backend 404.
    const dashboardSlug = (systemStore.consoleDashboardSlug || 'dash').toLowerCase();
    const isConsoleShellRoute = to.matched.some(record => record.meta.requiresAuth || record.meta.auth);
    const startsWithAllowedSlug =
        to.path.toLowerCase() === `/${dashboardSlug}`
        || to.path.toLowerCase().startsWith(`/${dashboardSlug}/`)
        || to.path.toLowerCase() === '/dash'
        || to.path.toLowerCase().startsWith('/dash/')
        || to.path.toLowerCase() === '/ja-dash'
        || to.path.toLowerCase().startsWith('/ja-dash/');

    if (
        !onPublicSite
        && isConsoleShellRoute
        && authStore.isAuthenticated
        && !startsWithAllowedSlug
    ) {
        return {
            path: `/${dashboardSlug}${to.path.startsWith('/') ? to.path : `/${to.path}`}`,
            query: to.query,
            hash: to.hash,
            replace: true,
        };
    }

    // Legacy nested URLs: `/dash/system/settings` → `/dash/settings`.
    // Do not rewrite `/dash/system` (System Info) or `/dash/system/notifications`.
    const legacySystemNested = new RegExp(
        `^/(?:${dashboardSlug}|dash|ja-dash)/system/([^/]+)`,
        'i',
    );
    const legacySystemMatch = to.path.match(legacySystemNested);
    if (legacySystemMatch) {
        const subPath = legacySystemMatch[1]?.toLowerCase() ?? '';
        const keepUnderSystemPrefix = new Set(['notifications']);
        if (!keepUnderSystemPrefix.has(subPath)) {
            const normalized = to.path.replace(
                new RegExp(`^/((?:${dashboardSlug}|dash|ja-dash))/system/`),
                '/$1/',
            );
            return { path: normalized, query: to.query, hash: to.hash, replace: true };
        }
    }

    if (!authStore.isAuthenticated && authStore.authBootstrapComplete) {
        const isPublicRoute = to.matched.some(record => record.meta.public);
        const isAuthRoute = ['login', 'register', 'forgot-password', 'reset-password', 'verify-email', 'maintenance', 'session-expired'].includes(to.name as string);

        if (!isPublicRoute && !isAuthRoute) {
            if (to.matched.some(record => record.meta.requiresAuth || record.meta.auth)) {
                return {
                    path: paths.loginPath,
                    query: { redirect: to.fullPath },
                    replace: true,
                };
            }
        }
    }

    // 3. Authentication & Guest Only Gatekeeping

    // Use CoreStore for maintenance mode (Unified Infrastructure)
    const isMaintenance = !!systemStore.maintenance.mode;
    const canBypassMaintenance = authStore.isAuthenticated && authStore.getRoleRank() >= 90;
    const isMaintenanceRoute = to.name === 'maintenance' || to.path === '/maintenance';
    const isLoginRoute = to.name === 'login' || to.path === paths.loginPath;

    if (isMaintenance && !canBypassMaintenance && !isMaintenanceRoute && !isLoginRoute) {
        rememberRouteBeforeError(from.fullPath, returnShell, authStore.isAuthenticated);
        return { path: '/maintenance' };
    }

    const requiresAuth = to.matched.some(record => record.meta.requiresAuth || record.meta.auth);
    const requiresGuest = to.matched.some(record => record.meta.guestOnly);

    if (requiresAuth && !authStore.isAuthenticated) {
        return {
            path: paths.loginPath,
            query: { redirect: to.fullPath },
            replace: true,
        };
    }

    if (requiresGuest && authStore.isAuthenticated) {
        if (to.name === 'member-login' || to.name === 'member-register') {
            return;
        }

        if (to.name === 'login' || to.name === 'register') {
            // Stale FE auth after 401 vapor lock — stay on login, do not bounce to /dash
            if (typeof window !== 'undefined' && window.__isSessionTerminated) {
                authStore.clearAuth();
                resetLockdown();
                return;
            }

            const slug = systemStore.consoleDashboardSlug || 'dash';
            return {
                path: `/${slug}`,
                replace: true,
            };
        }
    }

    const permissionMeta = to.meta.permission as string | undefined;
    const permissionsMeta = to.meta.permissions as string[] | undefined;
    const requiredPermissions: string[] = Array.isArray(permissionsMeta)
        ? permissionsMeta
        : (permissionMeta ? [permissionMeta] : []);

    if (requiredPermissions.length > 0) {
        const hasAll = requiredPermissions.every((p) => authStore.hasPermission(p));
        if (!hasAll) {
            return { path: '/403' };
        }
    }

    if (to.meta.requiresSuperAdmin && authStore.getRoleRank() < 100) {
        return { path: '/403' };
    }
};
