/** sys_settings key for the console dashboard URL prefix (e.g. ja-dash). */
export const CONSOLE_DASHBOARD_SLUG_KEY = 'console_dashboard_slug';

/** localStorage cache key (mirrors settings key). */
export const CONSOLE_DASHBOARD_SLUG_STORAGE_KEY = 'console_dashboard_slug';

export const DEFAULT_CONSOLE_DASHBOARD_SLUG = 'dash';

/** Slugs commonly seeded or set before public settings load (Vite dev server, first paint). */
export const FALLBACK_CONSOLE_DASHBOARD_SLUGS = [
    DEFAULT_CONSOLE_DASHBOARD_SLUG,
    'ja-dash',
] as const;

const readSlugFromStorage = (): string | undefined => {
    try {
        const stored = globalThis.localStorage?.getItem(CONSOLE_DASHBOARD_SLUG_STORAGE_KEY);
        if (stored?.trim()) {
            return stored.trim();
        }
    } catch {
        // ignore
    }

    return undefined;
};

/** Slug prefixes that resolve to the console shell (SPA pathname). */
export const collectConsoleDashboardSlugCandidates = (): string[] => {
    const slugs = new Set<string>(FALLBACK_CONSOLE_DASHBOARD_SLUGS);

    const fromEnv = typeof import.meta !== 'undefined'
        ? (import.meta as { env?: { VITE_CONSOLE_DASHBOARD_SLUG?: string } }).env?.VITE_CONSOLE_DASHBOARD_SLUG
        : undefined;
    if (typeof fromEnv === 'string' && fromEnv.trim() !== '') {
        slugs.add(fromEnv.trim());
    }

    const stored = readSlugFromStorage();
    if (stored) {
        slugs.add(stored);
    }

    return [...slugs];
};

export const pathUsesConsoleDashboardSlug = (pathname: string, slug: string): boolean => {
    const clean = pathname === '/' ? '/' : pathname.replace(/\/$/, '') || '/';
    const prefix = `/${slug}`;

    return clean === prefix || clean.startsWith(`${prefix}/`);
};

export const readConsoleDashboardSlugFromPayload = (
    data: Record<string, unknown> | null | undefined,
): string => {
    const raw = data?.[CONSOLE_DASHBOARD_SLUG_KEY];
    if (typeof raw === 'string' && raw.trim() !== '') {
        return raw.trim();
    }

    const stored = readSlugFromStorage();
    if (stored) {
        return stored;
    }

    return DEFAULT_CONSOLE_DASHBOARD_SLUG;
};

import type { RouteLocationRaw } from 'vue-router';

/** Console landing after login (`/:dashboard_slug` → dashboard redirect). */
export const resolveConsoleDashboardLocation = (slug?: string): RouteLocationRaw => {
    const dashboardSlug = slug ?? readSlugFromStorage() ?? DEFAULT_CONSOLE_DASHBOARD_SLUG;

    return {
        path: '/' + dashboardSlug,
    };
};

/** Build a console path: `/{slug}/{suffix}` (no tenant segment). */
export const buildConsolePath = (
    suffix: string,
    options?: { slug?: string },
): string => {
    const slug = options?.slug ?? readSlugFromStorage() ?? DEFAULT_CONSOLE_DASHBOARD_SLUG;
    const trimmed = suffix.replace(/^\//, '');

    return trimmed ? `/${slug}/${trimmed}` : `/${slug}`;
};

export const persistConsoleDashboardSlug = (slug: string): void => {
    try {
        globalThis.localStorage?.setItem(CONSOLE_DASHBOARD_SLUG_STORAGE_KEY, slug);
        globalThis.localStorage?.removeItem('admin_dashboard_slug');
    } catch {
        // ignore
    }
};
