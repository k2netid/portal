import {
    collectConsoleDashboardSlugCandidates,
    pathUsesConsoleDashboardSlug,
} from '@/config/console';
import { isConsoleShell } from '@/config/shell';
import {
    isLegitimateConsoleSlugPath,
    isProbePath,
    SECURITY_ROUTES,
    shouldBlockOnPublicSite,
} from '@/config/security';

/**
 * Error-page "Kembali" navigation (SPA):
 *
 * | Storage | Why |
 * |---------|-----|
 * | sessionStorage | Per-tab; cleared when tab closes. |
 * | Separate keys per shell | public vs console must not overwrite each other. |
 *
 * | Action | When |
 * |--------|------|
 * | Login + timeout query | `?timeout=1` on 404 — session expired flow (console). |
 * | Last safe route | Last successful in-app navigation in this tab (afterEach). |
 * | Explicit return | Router `from` when guard blocks probe/CMS miss (one-shot). |
 * | Referrer | Same-origin only; fallback when full page redirect skipped the router. |
 * | history.back() | Only if no safe target; abort if landing on probe/404 again. |
 * | Home | Public: `/`. Console guest: login. Console auth: dashboard slug. |
 *
 * We intentionally do NOT use `history.back()` alone (probe redirects create loops).
 */
/** Matches {@link AppShell}: public (marketing/member) vs console (operators). */
export type ErrorReturnShell = 'public' | 'console';

const storageKeys = (shell: ErrorReturnShell) => ({
    return: `ja_404_return_${shell}`,
    lastSafe: `ja_last_safe_route_${shell}`,
});

const pathOnly = (raw: string): string => {
    try {
        if (raw.includes('://')) {
            return new URL(raw).pathname;
        }
    } catch {
        return raw;
    }

    return raw.split('?')[0]?.split('#')[0] ?? raw;
};

const sameOriginFullPath = (raw: string): string | null => {
    try {
        if (!raw.includes('://')) {
            return raw;
        }

        const url = new URL(raw);
        if (url.origin !== window.location.origin) {
            return null;
        }

        return `${url.pathname}${url.search}${url.hash}`;
    } catch {
        return null;
    }
};

/** Unified error / maintenance screens (no back-target loops). */
export const ERROR_SCREEN_PATHS = new Set([
    SECURITY_ROUTES.notFound,
    '/403',
    '/419',
    '/429',
    '/500',
    '/maintenance',
]);

export const isErrorScreenPath = (raw: string): boolean => {
    const pathname = pathOnly(sameOriginFullPath(raw) ?? raw);
    return ERROR_SCREEN_PATHS.has(pathname);
};

/** Public site: CMS + marketing routes; blocks probe/console/auth. */
export const isSafePublicReturnPath = (raw: string): boolean => {
    const full = sameOriginFullPath(raw) ?? raw;
    if (!full || full.startsWith('//')) {
        return false;
    }

    const pathname = pathOnly(full);
    if (!pathname.startsWith('/') || pathname === SECURITY_ROUTES.notFound) {
        return false;
    }

    return !shouldBlockOnPublicSite(pathname);
};

/** Public auth wizard — track for "Kembali" only, not CMS/probe routes. */
export const isTrackablePublicPath = (raw: string): boolean => {
    if (isSafePublicReturnPath(raw)) {
        return true;
    }

    const pathname = pathOnly(sameOriginFullPath(raw) ?? raw);
    return pathname.startsWith('/auth/') || pathname.startsWith('/public/system/auth/');
};

/** Console shell: guests may only return to auth screens; operators to last console route. */
export const isSafeConsoleReturnPath = (raw: string, isAuthenticated: boolean): boolean => {
    const full = sameOriginFullPath(raw) ?? raw;
    if (!full || full.startsWith('//')) {
        return false;
    }

    const pathname = pathOnly(full);
    if (!pathname.startsWith('/') || pathname === SECURITY_ROUTES.notFound) {
        return false;
    }

    if (!isAuthenticated) {
        return (
            pathname === SECURITY_ROUTES.login
            || pathname === SECURITY_ROUTES.register
            || pathname.startsWith('/auth/')
        );
    }

    if (isProbePath(pathname) && !isLegitimateConsoleSlugPath(pathname)) {
        return false;
    }

    return (
        pathname === '/'
        || isLegitimateConsoleSlugPath(pathname)
        || collectConsoleDashboardSlugCandidates().some((slug) => pathUsesConsoleDashboardSlug(pathname, slug))
        || pathname.startsWith('/auth/')
    );
};

/** Derives shell from current pathname ({@link isConsoleShell}). */
export const errorReturnShell = (): ErrorReturnShell => (
    isConsoleShell() ? 'console' : 'public'
);

const isSafeForShell = (raw: string, shell: ErrorReturnShell, isAuthenticated: boolean): boolean => (
    shell === 'console'
        ? isSafeConsoleReturnPath(raw, isAuthenticated)
        : isSafePublicReturnPath(raw)
);

const isTrackableForShell = (raw: string, shell: ErrorReturnShell, isAuthenticated: boolean): boolean => (
    shell === 'public'
        ? isTrackablePublicPath(raw)
        : isSafeForShell(raw, shell, isAuthenticated)
);

/** Call after each successful navigation to a normal page in this shell. */
export const trackLastSafeRoute = (
    fullPath: string,
    shell: ErrorReturnShell = 'public',
    isAuthenticated = true,
): void => {
    if (!isTrackableForShell(fullPath, shell, isAuthenticated)) {
        return;
    }

    sessionStorage.setItem(storageKeys(shell).lastSafe, fullPath);
};

/** Auth screens: remember tab position without extra router hooks per view. */
export const trackAuthScreen = (fullPath: string): void => {
    trackLastSafeRoute(fullPath, errorReturnShell(), false);
};

export const getLastSafeRoute = (shell: ErrorReturnShell = 'public', isAuthenticated = true): string | null => {
    const last = sessionStorage.getItem(storageKeys(shell).lastSafe);
    return last && isSafeForShell(last, shell, isAuthenticated) ? last : null;
};

/** Strip unsafe redirect targets for login query (open redirect / error loops). */
export const sanitizeLoginRedirect = (
    candidate: unknown,
    currentErrorPath?: string,
): string | undefined => {
    if (typeof candidate !== 'string' || !candidate || candidate.startsWith('//')) {
        return undefined;
    }

    const pathname = pathOnly(candidate);
    if (currentErrorPath && pathname === pathOnly(currentErrorPath)) {
        return undefined;
    }

    if (isErrorScreenPath(candidate) || shouldBlockOnPublicSite(pathname)) {
        return undefined;
    }

    return candidate;
};

export const rememberRouteBeforeNotFound = (
    fromPath: string | undefined | null,
    shell: ErrorReturnShell = 'public',
    isAuthenticated = true,
): void => {
    const keys = storageKeys(shell);

    if (fromPath && isSafeForShell(fromPath, shell, isAuthenticated)) {
        sessionStorage.setItem(keys.return, fromPath);
        return;
    }

    const last = getLastSafeRoute(shell, isAuthenticated);
    if (last) {
        sessionStorage.setItem(keys.return, last);
        return;
    }

    if (!sessionStorage.getItem(keys.return)) {
        const fallback = shell === 'console'
            ? (isAuthenticated ? `/${collectConsoleDashboardSlugCandidates()[0] ?? 'dash'}` : SECURITY_ROUTES.login)
            : '/';
        sessionStorage.setItem(keys.return, fallback);
    }
};

const peekReferrerReturnPath = (shell: ErrorReturnShell, isAuthenticated: boolean): string | null => {
    try {
        const ref = document.referrer;
        if (!ref) {
            return null;
        }

        const url = new URL(ref);
        if (url.origin !== window.location.origin) {
            return null;
        }

        const full = `${url.pathname}${url.search}${url.hash}`;
        return isSafeForShell(full, shell, isAuthenticated) ? full : null;
    } catch {
        return null;
    }
};

/** Resolve where "Kembali" should go (does not clear last-safe history). */
export const resolveErrorReturnPath = (
    shell: ErrorReturnShell = 'public',
    isAuthenticated = true,
): string | null => {
    const keys = storageKeys(shell);
    const explicit = sessionStorage.getItem(keys.return);
    if (explicit && isSafeForShell(explicit, shell, isAuthenticated)) {
        return explicit;
    }

    const last = getLastSafeRoute(shell, isAuthenticated);
    if (last) {
        return last;
    }

    return peekReferrerReturnPath(shell, isAuthenticated);
};

export const consumeErrorReturnPath = (
    shell: ErrorReturnShell = 'public',
    isAuthenticated = true,
): string | null => {
    const resolved = resolveErrorReturnPath(shell, isAuthenticated);
    sessionStorage.removeItem(storageKeys(shell).return);
    return resolved;
};

/** Persists a one-shot return path before routing to any error screen (403–500, maintenance, 404). */
export const rememberRouteBeforeError = rememberRouteBeforeNotFound;

export type SessionExpiredReason = 'timeout' | 'concurrent';

export interface SessionExpiredOptions {
    reason: SessionExpiredReason;
    redirect?: string | null;
    currentPath?: string;
}

/** Canonical session-expired URL (SPA). */
export const buildSessionExpiredHref = (options: SessionExpiredOptions): string => {
    const shell = errorReturnShell();
    const current = options.currentPath
        ?? (typeof window !== 'undefined' ? `${window.location.pathname}${window.location.search}` : '/');

    rememberRouteBeforeError(current, shell, true);

    const redirect = sanitizeLoginRedirect(
        options.redirect ?? getLastSafeRoute(shell, true) ?? current,
        '/419',
    );

    const params = new URLSearchParams({ reason: options.reason });
    if (redirect) {
        params.set('redirect', redirect);
    }

    return `/419?${params.toString()}`;
};

/** Router navigation payload for session-expired (SPA, no full reload). */
export const buildSessionExpiredQuery = (
    options: SessionExpiredOptions & { currentPath: string },
): Record<string, string> => {
    const shell = errorReturnShell();
    rememberRouteBeforeError(options.currentPath, shell, true);

    const redirect = sanitizeLoginRedirect(
        options.redirect ?? getLastSafeRoute(shell, true) ?? options.currentPath,
        '/419',
    );

    const query: Record<string, string> = { reason: options.reason };
    if (redirect) {
        query.redirect = redirect;
    }

    return query;
};

export const defaultHomeForShell = (
    shell: ErrorReturnShell,
    isAuthenticated: boolean,
    consoleDashboardSlug = 'dash',
): string => {
    if (shell === 'console') {
        return isAuthenticated ? `/${consoleDashboardSlug}` : SECURITY_ROUTES.login;
    }

    return '/';
};

