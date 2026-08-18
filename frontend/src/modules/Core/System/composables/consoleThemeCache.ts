import type { ConsoleThemeSettings } from './useConsoleTheme';

export const CONSOLE_THEME_CACHE_KEY = 'console_theme_snapshot_v1';
export const CONSOLE_SETTINGS_CACHE_KEY = 'console_theme_settings_v1';

export interface ConsoleThemeSnapshot {
    mode?: string;
    cssVars: Record<string, string>;
    layoutAttrs: Record<string, string>;
}

export function writeConsoleThemeCache(
    settings: ConsoleThemeSettings,
    cssVars: Record<string, string>,
    layoutAttrs: Record<string, string>
) {
    if (typeof window === 'undefined') return;

    localStorage.setItem(CONSOLE_SETTINGS_CACHE_KEY, JSON.stringify(settings));

    const snapshot: ConsoleThemeSnapshot = {
        mode: layoutAttrs['data-console-theme-mode'],
        cssVars,
        layoutAttrs,
    };

    localStorage.setItem(CONSOLE_THEME_CACHE_KEY, JSON.stringify(snapshot));
}

export function readConsoleThemeSettingsCache(): ConsoleThemeSettings {
    if (typeof window === 'undefined') return {};
    const cached = localStorage.getItem(CONSOLE_SETTINGS_CACHE_KEY);
    if (cached) {
        try {
            return JSON.parse(cached) as ConsoleThemeSettings;
        } catch {
            return {};
        }
    }
    // Also try to read from old key for backward compatibility
    const oldCached = localStorage.getItem('ja_console_theme_cache');
    if (oldCached) {
        try {
            const parsed = JSON.parse(oldCached) as ConsoleThemeSettings;
            localStorage.setItem(CONSOLE_SETTINGS_CACHE_KEY, JSON.stringify(parsed));
            localStorage.removeItem('ja_console_theme_cache');
            return parsed;
        } catch {
            return {};
        }
    }
    return {};
}

export function hasConsoleThemeCache(): boolean {
    if (typeof window === 'undefined') return false;
    return !!localStorage.getItem(CONSOLE_THEME_CACHE_KEY);
}
