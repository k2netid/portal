/** Single Unified Console SPA Shell for Core Engine. */
export type AppShell = 'console' | 'public';

export const currentAppShell = (): AppShell => 'console';

export const isConsoleShell = (): boolean => true;

export const isPublicShell = (): boolean => false;
