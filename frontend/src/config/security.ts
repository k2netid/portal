import {
    collectConsoleDashboardSlugCandidates,
    pathUsesConsoleDashboardSlug,
} from './console';

export const SECURITY_ROUTES = {
    dashboardBase: '/dash',
    login: '/auth/console-sign-in',
    register: '/auth/console-sign-up',
    notFound: '/404',
} as const;

/**
 * Recon/scanner paths (WordPress, phpMyAdmin, generic /admin, /dashboard, …).
 * Must stay aligned with:
 * - backend/routes/web.php `$probePaths` sinkhole + throttle:probe-paths
 * - e2e tests/e2e/auth-probe-notfound.spec.ts
 *
 * Note: real Jejakawan console uses `/dash` or custom slug (e.g. `ja-dash`), not `/dashboard`.
 */
export const PROBE_PATH_PREFIXES = [
    '/admin',
    '/panel',
    '/dashboard',
    '/wp-admin',
    '/wp-login.php',
    '/phpmyadmin',
    '/pma',
    '/cpanel',
    '/administrator',
    '/manager',
    '/manage',
    '/system',
] as const;

/** Plain `/login` and `/register` paths (legacy bookmarks) treated like probe targets when unauthenticated. */
export const PUBLIC_AUTH_PATH_ALIASES = ['/login', '/register'] as const;

export const normalizePath = (rawPath: string): string => {
    try {
        return decodeURIComponent(rawPath).toLowerCase().replace(/\/{2,}/g, '/');
    } catch {
        return rawPath.toLowerCase().replace(/\/{2,}/g, '/');
    }
};

export const matchesPathPrefix = (path: string, prefixes: readonly string[]): boolean => {
    return prefixes.some((prefix) => path === prefix || path.startsWith(`${prefix}/`));
};

export const isProbePath = (path: string): boolean => {
    return matchesPathPrefix(normalizePath(path), PROBE_PATH_PREFIXES);
};

/** True when path uses the configured console URL prefix (dash, ja-dash, …). */
export const isLegitimateConsoleSlugPath = (path: string): boolean => (
    collectConsoleDashboardSlugCandidates().some((slug) => pathUsesConsoleDashboardSlug(path, slug))
);

/**
 * Guest hardening: return true → router shows 404 (hide admin surface).
 * Combines scanner probes, legacy /login bookmarks, and real console prefixes.
 * Operators should bookmark SECURITY_ROUTES.login, not /dash.
 */
export const isProtectedDashboardPath = (path: string): boolean => {
    const normalized = normalizePath(path);

    if (isLegitimateConsoleSlugPath(normalized)) {
        return true;
    }

    // Legacy default prefix if slug list is stale
    if (normalized.startsWith(SECURITY_ROUTES.dashboardBase)) {
        return true;
    }

    return false;
};

/** Guest hardening on public + console paths (probe sinkhole + hide operator URLs). */
export const shouldGuestReceiveSecurityNotFound = (path: string): boolean => (
    isProbePath(path)
    || PUBLIC_AUTH_PATH_ALIASES.includes(path as (typeof PUBLIC_AUTH_PATH_ALIASES)[number])
    || isProtectedDashboardPath(path)
);

/** Paths that must never render the public CMS shell (probe + console + auth). */
export const shouldBlockOnPublicSite = (path: string): boolean => {
    const normalized = normalizePath(path);

    if (normalized === '/member' || normalized.startsWith('/member/')) {
        return false;
    }

    if (normalized.startsWith('/auth/')) {
        return true;
    }

    return shouldGuestReceiveSecurityNotFound(path);
};

/** Reserved first-segment slugs for `/:slug` CMS route — scanners + infra words. */
const RESERVED_PUBLIC_CONTENT_SLUGS = new Set([
    'system',
    'security',
    'infra',
    'api',
    'sanctum',
    'auth',
    'install',
    'setup',
    'maintenance',
    'member',
    'admin',
    'panel',
    'dashboard',
    'manage',
]);

export const isReservedPublicContentSlug = (slug: string): boolean => {
    const s = slug.toLowerCase().replace(/^\/+|\/+$/g, '');
    if (RESERVED_PUBLIC_CONTENT_SLUGS.has(s)) return true;
    return collectConsoleDashboardSlugCandidates().some((c) => c.toLowerCase() === s);
};
