/** Dual SPA: console kernel vs public theme runtime. */
export type AppShell = 'console' | 'public';

let activeShell: AppShell = 'console';

export const setAppShell = (shell: AppShell): void => {
    activeShell = shell;
};

export const currentAppShell = (): AppShell => activeShell;

export const isConsoleShell = (): boolean => currentAppShell() === 'console';

export const isPublicShell = (): boolean => currentAppShell() === 'public';
