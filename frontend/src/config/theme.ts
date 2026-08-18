/** Operator console dark-mode preference (`localStorage`). */
export const CONSOLE_DARK_MODE_STORAGE_KEY = 'console-dark-mode';

export const readConsoleDarkModeFromStorage = (): string => {
    try {
        return globalThis.localStorage?.getItem(CONSOLE_DARK_MODE_STORAGE_KEY) ?? 'system';
    } catch {
        return 'system';
    }
};

export const persistConsoleDarkModeToStorage = (mode: string): void => {
    try {
        globalThis.localStorage?.setItem(CONSOLE_DARK_MODE_STORAGE_KEY, mode);
        // Drop pre-SPA key when persisting console preference.
        globalThis.localStorage?.removeItem('admin-dark-mode');
    } catch {
        // ignore
    }
};
