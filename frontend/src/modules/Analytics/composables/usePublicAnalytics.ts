import type { Router } from 'vue-router';
import api from '@/engine/api/client';
import { useSystemStore } from '@/modules/Core/System/stores/system';

const SESSION_KEY = 'ja_public_analytics_session';

const sessionId = (): string => {
    if (typeof sessionStorage === 'undefined') {
        return 'anon';
    }
    const existing = sessionStorage.getItem(SESSION_KEY);
    if (existing) {
        return existing;
    }
    const created = crypto.randomUUID();
    sessionStorage.setItem(SESSION_KEY, created);
    return created;
};

const ping = (path: string): void => {
    void api
        .post('/public/analytics/track-visit', {
            path,
            url: typeof window !== 'undefined' ? window.location.href : path,
            session_id: sessionId(),
        })
        .catch(() => {
            /* pack off or throttled — public shell still renders */
        });
};

/** Fire page views when pack analytics is product-active. */
export const installPublicAnalytics = (router: Router): void => {
    const systemStore = useSystemStore();
    const active = systemStore.activeExtensions ?? [];
    if (active.length > 0 && !active.includes('analytics')) {
        return;
    }

    ping(router.currentRoute.value.fullPath);
    router.afterEach((to) => {
        ping(to.fullPath);
    });
};
