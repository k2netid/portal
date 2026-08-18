import type { RouteLocationRaw } from 'vue-router';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import type { NavItem } from '@/shared/utils/navigation';

/** Console dashboard slug from settings (default `dash`). */
export function consoleDashboardSlug(): string {
    try {
        return useSystemStore().consoleDashboardSlug || 'dash';
    } catch {
        // When called before Pinia is active (early bootstrap), fall back safely.
        return 'dash';
    }
}

/** Absolute path under the console shell, e.g. `/dash/contents`. */
export function consolePath(segment: string): string {
    const slug = consoleDashboardSlug();
    const normalized = segment.startsWith('/') ? segment : `/${segment}`;
    return `/${slug}${normalized}`;
}

/** Named child route with dashboard slug param preserved. */
export function consoleNamedRoute(
    name: string,
    params: Record<string, string> = {},
    query?: Record<string, string | string[]>,
): RouteLocationRaw {
    return {
        name,
        params: { dashboard_slug: consoleDashboardSlug(), ...params },
        ...(query ? { query } : {}),
    };
}

function isValidRouteObjectTarget(value: unknown): value is Exclude<RouteLocationRaw, string> {
    if (!value || typeof value !== 'object') return false;
    const record = value as Record<string, unknown>;
    return Boolean(record.name || record.path);
}

/** Resolve sidebar / command palette nav targets; never return empty string. */
export function resolveNavTo(item: Pick<NavItem, 'to' | 'name'>): RouteLocationRaw | null {
    if (item.to !== undefined && item.to !== null && item.to !== '') {
        // Rewrite any hardcoded '/dash/' prefix to the current slug
        if (typeof item.to === 'string' && item.to.startsWith('/dash/')) {
            return `/${consoleDashboardSlug()}/${item.to.slice(6)}`;
        }
        if (typeof item.to === 'string') {
            return item.to;
        }
        if (isValidRouteObjectTarget(item.to)) {
            return item.to;
        }
        return null;
    }
    if (item.name) {
        try {
            return consoleNamedRoute(item.name);
        } catch {
            // Guard: if consoleDashboardSlug() throws before store is ready, fall back to path
            return `/${item.name}`;
        }
    }
    return null;
}
