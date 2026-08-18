import { logger } from '@/shared/utils/logger';
import type { RouteLocationNormalized } from 'vue-router';
import { consumeApiPerfEntries } from '@/engine/api/client';
import { AnalyticsService } from '@/shared/services/analyticsService';
import { SECURITY_ROUTES } from '@/config/security';
import axios from 'axios';

let initialNavigationDone = false;
let lastTrackedPath = '';
let lastTrackedAt = 0;
let analyticsBlockedUntil = 0;
let routePerfStartedAt = performance.now();

const reportRoutePerf = (to: RouteLocationNormalized, from: RouteLocationNormalized): void => {
    const routeDurationMs = Math.max(0, performance.now() - routePerfStartedAt);
    routePerfStartedAt = performance.now();

    const entries = consumeApiPerfEntries();
    if (entries.length === 0) {
        logger.debug('[Perf] Route transition', {
            from: from.path,
            to: to.path,
            routeDurationMs: Math.round(routeDurationMs),
            apiCalls: 0,
        });
        return;
    }

    const sorted = [...entries].sort((a, b) => b.durationMs - a.durationMs);
    const topSlow = sorted.slice(0, 5).map((e) => ({
        url: e.url,
        method: e.method,
        status: e.status,
        durationMs: Math.round(e.durationMs),
    }));

    logger.debug('[Perf] Route transition', {
        from: from.path,
        to: to.path,
        routeDurationMs: Math.round(routeDurationMs),
        apiCalls: entries.length,
        slowestApi: topSlow,
    });
};

export const trackRouteVisit = (to: RouteLocationNormalized, from: RouteLocationNormalized): void => {
    reportRoutePerf(to, from);

    if (Date.now() < analyticsBlockedUntil) {
        return;
    }

    if (!initialNavigationDone) {
        initialNavigationDone = true;
        return;
    }

    if (to.path.startsWith(SECURITY_ROUTES.dashboardBase) || to.name === 'maintenance' || to.path === '/maintenance') {
        return;
    }

    if (to.path === from.path) {
        return;
    }

    const now = Date.now();
    if (to.path === lastTrackedPath && now - lastTrackedAt < 5000) {
        return;
    }

    lastTrackedPath = to.path;
    lastTrackedAt = now;

    const payload = {
        url: window.location.href,
        path: to.path,
        title: document.title,
    };

    const send = () => {
        AnalyticsService.trackVisit(payload).catch((err) => {
            if (axios.isAxiosError(err) && err.response?.status === 403) {
                // Avoid noisy retry loops while edge protection is blocking this endpoint.
                analyticsBlockedUntil = Date.now() + 5 * 60 * 1000;
                return;
            }
            logger.debug('Analytics tracking failed:', err);
        });
    };

    if (typeof requestIdleCallback === 'function') {
        requestIdleCallback(send, { timeout: 2500 });
    } else {
        requestAnimationFrame(send);
    }
};
