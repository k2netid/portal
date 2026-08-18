import { watchEffect, onScopeDispose } from 'vue';
import { storeToRefs } from 'pinia';
import { useConsoleTheme } from '@/modules/Core/System/composables/useConsoleTheme';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { applyFavicon, resolveFavicon } from '@/modules/Core/System/utils/favicon';

const THEME_FLAG = 'data-console-theme';
const THEME_ATTRS = ['data-console-preset', 'data-console-surface', 'data-console-glass-gradient', 'data-console-theme-mode', 'data-console-button-style', 'data-console-card-style', 'data-console-dropdown-style', 'data-console-icon-weight'] as const;
const THEME_VARS = [
    '--console-radius',
    '--console-primary',
    '--console-primary-hsl',
    '--console-primary-foreground-hsl',
    '--console-glass-bg-image',
    '--console-sidebar-bg',
    '--console-sidebar-border',
    '--console-sidebar-text',
    '--console-sidebar-accent-hsl',
    '--sidebar-accent',
    '--sidebar-accent-foreground',
    '--console-navbar-bg',
    '--console-navbar-border',
    '--console-popper-bg',
    '--console-popper-border',
    '--console-popper-blur',
    '--console-font-sans',
    '--console-font-mono',
    '--console-shadow',
    '--console-border-width',
    '--console-icon-stroke',
    '--console-modal-backdrop-blur',
    '--console-modal-backdrop-alpha',
] as const;

/**
 * Mirrors console theme tokens onto <html> so Radix portals (dialog, popover, dropdown)
 * and fixed chrome (sidebar, navbar) inherit the same CSS variables as .admin-layout.
 */
export function useConsoleThemeDocumentSync() {
    const { cssVars, layoutAttrs, settings } = useConsoleTheme();
    const systemStore = useSystemStore();
    const { appIdentity, siteSettings } = storeToRefs(systemStore);

    watchEffect(() => {
        const root = document.documentElement;
        root.setAttribute(THEME_FLAG, '');

        for (const attr of THEME_ATTRS) {
            const value = layoutAttrs.value[attr];
            if (value) {
                root.setAttribute(attr, value);
            } else {
                root.removeAttribute(attr);
            }
        }

        const vars = cssVars.value;
        for (const key of THEME_VARS) {
            const value = vars[key];
            if (value) {
                root.style.setProperty(key, value);
            } else {
                root.style.removeProperty(key);
            }
        }

        applyFavicon(resolveFavicon([
            settings.value.app_favicon,
            appIdentity.value.app_favicon,
            siteSettings.value.site_favicon,
        ]));

        // Dynamically load Google Fonts if custom font is active
        const fontPrimary = settings.value.console_font_primary;
        if (fontPrimary && fontPrimary !== 'system') {
            const fontName = fontPrimary === 'plus_jakarta' ? 'Plus+Jakarta+Sans' : fontPrimary.charAt(0).toUpperCase() + fontPrimary.slice(1);
            let link = document.getElementById('console-font-primary') as HTMLLinkElement;
            if (!link) {
                link = document.createElement('link');
                link.id = 'console-font-primary';
                link.rel = 'stylesheet';
                document.head.appendChild(link);
            }
            link.href = `https://fonts.googleapis.com/css2?family=${fontName}:wght@300;400;500;600;700&display=swap`;
        }

        const fontMono = settings.value.console_font_mono;
        if (fontMono && fontMono !== 'system') {
            const fontName = fontMono === 'jetbrains_mono' ? 'JetBrains+Mono' : 'Fira+Code';
            let link = document.getElementById('console-font-mono') as HTMLLinkElement;
            if (!link) {
                link = document.createElement('link');
                link.id = 'console-font-mono';
                link.rel = 'stylesheet';
                document.head.appendChild(link);
            }
            link.href = `https://fonts.googleapis.com/css2?family=${fontName}:wght@300;400;500;600;700&display=swap`;
        }
    });

    onScopeDispose(() => {
        const root = document.documentElement;
        root.removeAttribute(THEME_FLAG);
        for (const attr of THEME_ATTRS) {
            root.removeAttribute(attr);
        }
        for (const key of THEME_VARS) {
            root.style.removeProperty(key);
        }
    });
}
