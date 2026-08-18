import type { ConsoleThemeSettings } from '@/modules/Core/System/composables/useConsoleTheme';

export type ResolveConsoleSidebarLogoOptions = {
    minimized?: boolean;
    isDark?: boolean;
    legacyLogo?: string;
};

/** Pick sidebar logo URL from console theme settings (light / dark / compact). */
export function resolveConsoleSidebarLogo(
    settings: Partial<ConsoleThemeSettings>,
    options: ResolveConsoleSidebarLogoOptions = {},
): string {
    const { minimized = false, isDark = false, legacyLogo = '' } = options;
    const light = String(settings.app_logo_light || legacyLogo || '').trim();
    const dark = String(settings.app_logo_dark || light || legacyLogo || '').trim();
    const compact = String(settings.app_logo_compact || '').trim();

    if (minimized) {
        return compact || (isDark ? dark : light);
    }
    return isDark ? dark : light;
}
