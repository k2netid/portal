import {
    collectConsoleDashboardSlugCandidates,
    pathUsesConsoleDashboardSlug,
} from '../../config/console';
import { SECURITY_ROUTES } from '../../config/security';

const CONSOLE_ENTRYPOINT_PATHS = [
    SECURITY_ROUTES.dashboardBase,
    SECURITY_ROUTES.login,
    SECURITY_ROUTES.register,
    '/login',
    '/register',
    '/public/system/auth/forgot-password',
    '/public/system/auth/reset-password',
    '/forgot-password',
    '/reset-password',
    '/verify-email',
] as const;

const SHARED_SYSTEM_PATHS = [
    '/403',
    '/404',
    '/500',
    '/419',
    '/429',
    '/maintenance',
    '/install',
] as const;

const normalizeEntryPath = (pathname: string): string => (
    pathname === '/' ? '/' : pathname.replace(/\/$/, '') || '/'
);

const matchesConsoleEntrypoint = (
    cleanPath: string,
    dashboardSlugs: readonly string[] = collectConsoleDashboardSlugCandidates(),
): boolean => {
    if (cleanPath === '/' || cleanPath === '') {
        return false;
    }

    if (dashboardSlugs.some((slug) => pathUsesConsoleDashboardSlug(cleanPath, slug))) {
        return true;
    }

    return CONSOLE_ENTRYPOINT_PATHS.some((prefix) => (
        cleanPath === prefix || cleanPath.startsWith(`${prefix}/`)
    ));
};

/** Route-based shell: console vs public (single SPA bootstrap). */
export const resolveIsConsoleEntrypoint = (pathname: string): boolean => {
    const cleanPath = normalizeEntryPath(pathname);

    if (SHARED_SYSTEM_PATHS.some((p) => cleanPath === p || cleanPath.startsWith(`${p}/`))) {
        return false;
    }

    return matchesConsoleEntrypoint(cleanPath, collectConsoleDashboardSlugCandidates());
};
