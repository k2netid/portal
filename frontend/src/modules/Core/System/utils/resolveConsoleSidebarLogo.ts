import type { ConsoleThemeSettings } from '@/modules/Core/System/composables/useConsoleTheme';

export type ResolveConsoleSidebarLogoOptions = {
    minimized?: boolean;
    isDark?: boolean;
    legacyLogo?: string;
    brandLogo?: string;
};

/** Pick sidebar logo URL from console theme settings (light / dark / compact). */
export function resolveConsoleSidebarLogo(
    settings: Partial<ConsoleThemeSettings>,
    options: ResolveConsoleSidebarLogoOptions = {},
): string {
    const { minimized = false, isDark = false, legacyLogo = '', brandLogo = '' } = options;
    const fallbackLogo = String(brandLogo || legacyLogo || '/logo.png').trim();
    const light = String(settings.app_logo_light || fallbackLogo).trim();
    const dark = String(settings.app_logo_dark || light || fallbackLogo).trim();
    const compact = String(settings.app_logo_compact || '').trim();

    if (minimized) {
        return compact || (isDark ? dark : light);
    }
    return isDark ? dark : light;
}
