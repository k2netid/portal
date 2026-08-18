import { resolveIsConsoleEntrypoint } from '@/engine/router/entrypoint';

/** Single SPA: console (operators) vs public (hub landing + member). */
export type AppShell = 'console' | 'public';

export const currentAppShell = (): AppShell => {
    if (typeof window === 'undefined') {
        return 'public';
    }

    return resolveIsConsoleEntrypoint(window.location.pathname) ? 'console' : 'public';
};

export const isConsoleShell = (): boolean => currentAppShell() === 'console';

export const isPublicShell = (): boolean => currentAppShell() === 'public';
