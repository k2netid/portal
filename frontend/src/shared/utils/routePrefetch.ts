import type { Router, RouteLocationRaw } from 'vue-router';
import { preloadLocalesForRoute } from '@/engine/i18n/deferredLocales';

const prefetchedKeys = new Set<string>();

/**
 * Warm lazy route chunks before navigation (e.g. sidebar hover).
 */
export function prefetchRoute(router: Router, to: RouteLocationRaw): void {
    if (typeof window === 'undefined') {
        return;
    }

    let resolved;
    try {
        resolved = router.resolve(to);
    } catch {
        return;
    }

    const key = resolved.fullPath;
    if (prefetchedKeys.has(key)) {
        return;
    }
    prefetchedKeys.add(key);

    void preloadLocalesForRoute(resolved.path, resolved.name);

    for (const record of resolved.matched) {
        const component = record.components?.default;
        if (typeof component === 'function') {
            void (component as () => Promise<unknown>)().catch(() => {});
        }
    }
}

/** Prefetch common console hub routes during idle time after login. */
export function prefetchConsoleHubRoutes(router: Router): void {
    const names = [
        'dashboard',
        'security',
        'crm',
        'accounting',
        'extensions',
        'system-settings',
        'publishing-settings',
    ];

    const run = () => {
        for (const name of names) {
            try {
                prefetchRoute(router, { name });
            } catch {
                /* route may not exist for current context */
            }
        }
    };

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(() => run(), { timeout: 4000 });
    } else {
        setTimeout(run, 1200);
    }
}
