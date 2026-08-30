/** Dual SPA + kernel landing when Site pack is off. */
export type AppShell = 'console' | 'public' | 'landing';

let activeShell: AppShell = 'console';

export const setAppShell = (shell: AppShell): void => {
    activeShell = shell;
};

export const currentAppShell = (): AppShell => activeShell;

export const isConsoleShell = (): boolean => currentAppShell() === 'console';

export const isPublicShell = (): boolean => currentAppShell() === 'public';

export const isLandingShell = (): boolean => currentAppShell() === 'landing';
