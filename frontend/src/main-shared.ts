
import { createApp, type App, type Component } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { createHead } from '@unhead/vue/client';
import lazyLoad from '@/shared/utils/directives/lazyLoad';
import i18n from '@/engine/i18n';
import { readConsoleDarkModeFromStorage } from '@/config/theme';
import { attemptChunkRecoveryReload, isChunkLoadError } from '@/shared/utils/chunkRecovery';
import type { Router } from 'vue-router';

export type ShellKind = 'console' | 'public';

if (import.meta.env.PROD) {
    (window as Window & { __APP_BUILD_ID__?: string }).__APP_BUILD_ID__ = __APP_BUILD_ID__;
}

window.onerror = function (message, source, lineno, colno, error) {
    if (isChunkLoadError(error || message) && attemptChunkRecoveryReload()) {
        return true;
    }

    const safeUrl = (() => {
        try {
            const parsed = new URL(window.location.href);
            return parsed.origin + parsed.pathname;
        } catch {
            return window.location.pathname || '/';
        }
    })();

    fetch('/api/v1/journal/frontend', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            level: 'error',
            message: 'Startup Crash: ' + String(message),
            data: {
                message,
                source,
                lineno,
                colno,
                stack: error?.stack,
                url: safeUrl,
                timestamp: new Date().toISOString(),
            },
        }),
    }).catch(() => {});

    return false;
};

export function initShellLayout(shell: ShellKind): void {
    document.documentElement.classList.add('no-transitions');

    const saved = shell === 'console'
        ? readConsoleDarkModeFromStorage()
        : (localStorage.getItem('frontend-dark-mode') ?? null);

    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    const isDark = saved === 'dark'
        || (saved === 'system' && mq.matches)
        || (!saved && mq.matches);

    if (isDark) {
        document.documentElement.classList.add('dark');
    } else if (saved === 'light') {
        document.documentElement.classList.remove('dark');
    }

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            document.documentElement.classList.remove('no-transitions');
        });
    });
}

export function createShellApp(RootComponent: Component): App {
    const app = createApp(RootComponent);

    app.config.errorHandler = (err) => {
        if (isChunkLoadError(err) && attemptChunkRecoveryReload()) return;
        if (import.meta.env.DEV) console.error('[VUE_ERROR]', err);
    };

    const pinia = createPinia();
    pinia.use(piniaPluginPersistedstate);
    const head = createHead();

    app.use(pinia);
    app.use(head);
    app.use(i18n);
    app.directive('lazy', lazyLoad);

    return app;
}

export async function useLoggerPlugin(app: App) {
    const { default: Logger, logger } = await import('@/shared/utils/logger');
    app.use(Logger);
    return { logger };
}

export type ShellBootstrapContext = {
    app: App;
    router: Router;
    logger: { info: (...args: unknown[]) => void; warning: (...args: unknown[]) => void };
};

// @ts-expect-error audit global
window.JANARI_VERSION = '2026.05.21.hub-spa';
