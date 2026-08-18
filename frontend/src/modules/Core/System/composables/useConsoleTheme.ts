import { computed, ref } from 'vue';
import api from '@/engine/api/client';
import { logger } from '@/shared/utils/logger';
import { hexToHslComponents, normalizeRadiusPx } from '@/shared/utils/color';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import {
    CONSOLE_COLOR_PRESET_CUSTOM,
    CONSOLE_SURFACE_GLASS,
    getPresetHslTokens,
    normalizeConsoleColorPreset,
    normalizeConsoleSurfaceStyle,
    type ConsoleColorPresetId,
    type ConsoleSurfaceStyle,
} from '@/modules/Core/System/constants/consoleThemePresets';
import {
    buildConsoleGlassBackgroundImage,
    clampGlassAngle,
    clampGlassIntensity,
    CONSOLE_GLASS_GRADIENT_DEFAULT,
    normalizeConsoleGlassGradientPreset,
    type ConsoleGlassGradientPresetId,
} from '@/modules/Core/System/constants/consoleGlassGradient';
import {
    CONSOLE_THEME_MODE_GLOBAL,
    deriveGlobalShellSettings,
    normalizeConsoleThemeMode,
    type ConsoleThemeMode,
} from '@/modules/Core/System/constants/consoleThemeMode';
import {
    clampModalBackdropOpacity,
    normalizeConsoleButtonStyle,
    normalizeConsoleCardStyle,
    normalizeConsoleDropdownStyle,
    normalizeConsoleIconWeight,
} from '@/modules/Core/System/constants/consoleAdvancedComponents';
import { readConsoleThemeSettingsCache, writeConsoleThemeCache, hasConsoleThemeCache } from './consoleThemeCache';

export interface ConsoleThemeSettings {
    console_theme_mode?: string;
    console_color_preset?: string;
    console_brand_primary?: string;
    console_brand_primary_dark?: string;
    console_button_radius?: number | string;
    console_surface_style?: string;
    console_sidebar_accent?: string;
    console_sidebar_style?: string;
    console_navbar_style?: string;
    console_popper_opacity?: number | string;
    console_glass_gradient_preset?: string;
    console_glass_gradient_color?: string;
    console_glass_gradient_intensity?: number | string;
    console_glass_gradient_angle?: number | string;
    app_logo_light?: string;
    app_logo_dark?: string;
    app_logo_compact?: string;
    app_favicon?: string;
    console_font_primary?: string;
    console_font_mono?: string;
    console_shadow_elevation?: string;
    console_border_style?: string;
    console_icon_weight?: string;
    console_dropdown_style?: string;
    console_modal_backdrop_opacity?: number | string;
    console_card_style?: string;
    console_button_style?: string;
}

const settings = ref<ConsoleThemeSettings>(readConsoleThemeSettingsCache());
const loaded = ref(false);
let loadPromise: Promise<ConsoleThemeSettings> | null = null;
const isThemeBootstrapped = ref(hasConsoleThemeCache());

export function whenConsoleThemeBootstrapped(): Promise<void> {
    if (isThemeBootstrapped.value) return Promise.resolve();
    if (loadPromise) return loadPromise.then(() => {});
    return Promise.resolve();
}

const DEFAULT_PRIMARY = '#4f46e5';


function normalizeSidebarAccentHex(hex: string | undefined): string | null {
    if (!hex || typeof hex !== 'string') return null;
    const trimmed = hex.trim();
    if (!/^#[0-9A-Fa-f]{6}$/.test(trimmed)) return null;
    return trimmed;
}

function applySidebarAccentVars(vars: Record<string, string>, accentHex: string | undefined) {
    const hex = normalizeSidebarAccentHex(accentHex);
    if (!hex) return;
    const hsl = hexToHslComponents(hex);
    if (!hsl) return;
    vars['--console-sidebar-accent-hsl'] = hsl.hsl;
    vars['--sidebar-accent'] = hsl.hsl;
    if (hsl.foregroundHsl) {
        vars['--sidebar-accent-foreground'] = hsl.foregroundHsl;
    }
}

function applySidebarVars(
    vars: Record<string, string>,
    sidebarStyle: string,
    isDark: boolean,
    primaryHex: string,
) {
    if (sidebarStyle === 'solid') {
        vars['--console-sidebar-bg'] = vars['--console-primary'] ?? primaryHex;
        vars['--console-sidebar-border'] = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.08)';
        vars['--console-sidebar-text'] = '0 0% 100%';
    } else if (sidebarStyle === 'clean') {
        vars['--console-sidebar-bg'] = isDark ? 'hsl(0 0% 4%)' : 'hsl(0 0% 100%)';
        vars['--console-sidebar-border'] = isDark ? 'hsl(0 0% 16%)' : 'hsl(0 0% 90%)';
        vars['--console-sidebar-text'] = isDark ? '0 0% 98%' : '0 0% 6%';
    } else {
        vars['--console-sidebar-bg'] = isDark ? 'rgba(0, 0, 0, 0.72)' : 'rgba(255, 255, 255, 0.72)';
        vars['--console-sidebar-border'] = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.08)';
        vars['--console-sidebar-text'] = isDark ? '0 0% 98%' : '0 0% 6%';
    }
}

function applyNavbarVars(vars: Record<string, string>, navbarStyle: string, isDark: boolean) {
    if (navbarStyle === 'bordered') {
        vars['--console-navbar-bg'] = isDark ? 'hsl(0 0% 4%)' : 'hsl(0 0% 100%)';
        vars['--console-navbar-border'] = isDark ? 'hsl(0 0% 16%)' : 'hsl(0 0% 90%)';
    } else if (navbarStyle === 'blended') {
        vars['--console-navbar-bg'] = 'transparent';
        vars['--console-navbar-border'] = 'transparent';
    } else {
        vars['--console-navbar-bg'] = isDark ? 'rgba(4, 4, 4, 0.65)' : 'rgba(255, 255, 255, 0.65)';
        vars['--console-navbar-border'] = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)';
    }
}

function applyDropdownVars(
    vars: Record<string, string>,
    dropdownStyle: string,
    popperOpacityPct: number,
    isDark: boolean,
) {
    const style = normalizeConsoleDropdownStyle(dropdownStyle);
    const baseOpacity = clampGlassIntensity(popperOpacityPct, 85) / 100;
    let opacity = baseOpacity;
    let blur = '16px';
    if (style === 'minimal') {
        opacity = Math.min(1, baseOpacity + 0.08);
        blur = '0px';
    } else if (style === 'glass') {
        opacity = Math.max(0.55, baseOpacity - 0.12);
        blur = '20px';
    }
    vars['--console-popper-bg'] = isDark
        ? `rgba(10, 10, 10, ${opacity})`
        : `rgba(255, 255, 255, ${opacity})`;
    vars['--console-popper-border'] = isDark ? 'rgba(255, 255, 255, 0.09)' : 'rgba(0, 0, 0, 0.09)';
    vars['--console-popper-blur'] = blur;
}

function applyModalBackdropVars(vars: Record<string, string>, opacityPct: number) {
    const pct = clampModalBackdropOpacity(opacityPct) / 100;
    vars['--console-modal-backdrop-alpha'] = String(pct);
    vars['--console-modal-backdrop-blur'] = pct >= 0.6 ? '6px' : '4px';
}

function applyCardStyleVars(
    vars: Record<string, string>,
    s: ConsoleThemeSettings,
    isDark: boolean,
) {
    const cardStyle = normalizeConsoleCardStyle(s.console_card_style);
    let shadowKey: string;
    let borderKey: string;
    if (cardStyle === 'flat') {
        shadowKey = 'flat';
        borderKey = 'subtle';
    } else if (cardStyle === 'elevated') {
        shadowKey = 'layered';
        borderKey = 'subtle';
    } else {
        shadowKey = 'soft';
        borderKey = 'subtle';
    }

    if (shadowKey === 'flat') {
        vars['--console-shadow'] = 'none';
    } else if (shadowKey === 'layered') {
        vars['--console-shadow'] = isDark
            ? '0 10px 30px -10px rgba(0, 0, 0, 0.45), 0 1px 3px rgba(0, 0, 0, 0.2)'
            : '0 10px 30px -10px rgba(0, 0, 0, 0.12), 0 1px 3px rgba(0, 0, 0, 0.05)';
    } else {
        vars['--console-shadow'] = isDark
            ? '0 2px 12px -2px rgba(0, 0, 0, 0.25), 0 4px 20px -2px rgba(0, 0, 0, 0.2)'
            : '0 2px 12px -2px rgba(0, 0, 0, 0.05), 0 4px 20px -2px rgba(0, 0, 0, 0.04)';
    }

    if (borderKey === 'clean') {
        vars['--console-border-width'] = '0px';
    } else if (borderKey === 'bold') {
        vars['--console-border-width'] = '2px';
    } else {
        vars['--console-border-width'] = '1px';
    }
}

function applyIconWeightVars(vars: Record<string, string>, weight: string) {
    const w = normalizeConsoleIconWeight(weight);
    const stroke = w === 'light' ? '1.5' : w === 'bold' ? '2.5' : '2';
    vars['--console-icon-stroke'] = stroke;
}

function applyTypographyVars(vars: Record<string, string>, s: ConsoleThemeSettings) {
    const fontPrimary = s.console_font_primary ?? 'system';
    const fontSansMap = {
        system: 'ui-sans-serif, system-ui, sans-serif',
        inter: "'Inter', ui-sans-serif, system-ui, sans-serif",
        outfit: "'Outfit', ui-sans-serif, system-ui, sans-serif",
        poppins: "'Poppins', ui-sans-serif, system-ui, sans-serif",
    };
    vars['--console-font-sans'] = fontSansMap[fontPrimary as keyof typeof fontSansMap] ?? fontSansMap.system;

    const fontMono = s.console_font_mono ?? 'system';
    const fontMonoMap = {
        system: 'ui-monospace, SFMono-Regular, Consolas, monospace',
        jetbrains_mono: "'JetBrains Mono', ui-monospace, SFMono-Regular, Consolas, monospace",
        fira_code: "'Fira Code', ui-monospace, SFMono-Regular, Consolas, monospace",
    };
    vars['--console-font-mono'] = fontMonoMap[fontMono as keyof typeof fontMonoMap] ?? fontMonoMap.system;
}


function isMemberConsolePath(): boolean {
    return typeof window !== 'undefined' && window.location.pathname.startsWith('/member');
}

/**
 * Console (admin) appearance — schema-backed settings from API.
 * Global vs advanced modes are mutually exclusive when computing CSS variables.
 */
export function useConsoleTheme() {
    const systemStore = useSystemStore();

    const load = async (force = false): Promise<ConsoleThemeSettings> => {
        if (loaded.value && !force) {
            return settings.value;
        }
        if (force) {
            loaded.value = false;
            loadPromise = null;
        }
        if (loadPromise) {
            return loadPromise;
        }

        loadPromise = (async () => {
            if (isMemberConsolePath()) {
                try {
                    const response = await api.get('/public/system/console-theme');
                    const payload = response.data as { settings?: ConsoleThemeSettings } | ConsoleThemeSettings;
                    const resolved =
                        payload && typeof payload === 'object' && 'settings' in payload
                            ? (payload.settings ?? {})
                            : (payload ?? {});
                    settings.value = resolved as ConsoleThemeSettings;
                    // Note: CSS vars and layout attrs are computed, so we need to wait for them to update
                    setTimeout(() => {
                        writeConsoleThemeCache(settings.value, (useConsoleTheme() as any).cssVars.value, (useConsoleTheme() as any).layoutAttrs.value);
                    }, 0);
                    isThemeBootstrapped.value = true;
                } catch (error) {
                    logger.error('[useConsoleTheme] Failed to load public console theme for Jejakawan:', error);
                    settings.value = {};
                } finally {
                    loaded.value = true;
                    loadPromise = null;
                }
                return settings.value;
            }
            try {
                const response = await api.get('/manage/system/console-theme');
                const data = response.data?.settings ?? response.data ?? {};
                settings.value = data as ConsoleThemeSettings;
                setTimeout(() => {
                    writeConsoleThemeCache(settings.value, (useConsoleTheme() as any).cssVars.value, (useConsoleTheme() as any).layoutAttrs.value);
                }, 0);
                isThemeBootstrapped.value = true;
            } catch (error) {
                logger.error('[useConsoleTheme] Failed to load console theme:', error);
                try {
                    const fallback = await api.get('/manage/system/settings/group/console_branding');
                    settings.value = (fallback.data ?? {}) as ConsoleThemeSettings;
                    setTimeout(() => {
                        writeConsoleThemeCache(settings.value, (useConsoleTheme() as any).cssVars.value, (useConsoleTheme() as any).layoutAttrs.value);
                    }, 0);
                    isThemeBootstrapped.value = true;
                } catch {
                    settings.value = {};
                    isThemeBootstrapped.value = true;
                }
            } finally {
                loaded.value = true;
                loadPromise = null;
            }
            return settings.value;
        })();

        return loadPromise;
    };

    const applyDraft = (partial: Partial<ConsoleThemeSettings>) => {
        settings.value = { ...settings.value, ...partial };
    };

    const themeMode = computed((): ConsoleThemeMode =>
        normalizeConsoleThemeMode(settings.value.console_theme_mode),
    );

    const isGlobalMode = computed(() => themeMode.value === CONSOLE_THEME_MODE_GLOBAL);

    const colorPreset = computed((): ConsoleColorPresetId =>
        normalizeConsoleColorPreset(settings.value.console_color_preset),
    );

    const surfaceStyle = computed((): ConsoleSurfaceStyle =>
        normalizeConsoleSurfaceStyle(settings.value.console_surface_style ?? CONSOLE_SURFACE_GLASS),
    );

    const isCustomPreset = computed(() => colorPreset.value === CONSOLE_COLOR_PRESET_CUSTOM);

    const primaryHex = computed(
        () => String(settings.value.console_brand_primary || DEFAULT_PRIMARY),
    );

    const primaryDarkHex = computed(
        () => String(settings.value.console_brand_primary_dark || settings.value.console_brand_primary || '#818cf8'),
    );

    const glassGradientPreset = computed((): ConsoleGlassGradientPresetId =>
        normalizeConsoleGlassGradientPreset(
            settings.value.console_glass_gradient_preset ?? CONSOLE_GLASS_GRADIENT_DEFAULT,
        ),
    );

    const glassGradientIntensity = computed(() =>
        clampGlassIntensity(settings.value.console_glass_gradient_intensity, 55),
    );

    const glassGradientAngle = computed(() =>
        clampGlassAngle(settings.value.console_glass_gradient_angle, 135),
    );

    const glassGradientColorHex = computed(() =>
        String(settings.value.console_glass_gradient_color || primaryHex.value),
    );

    /** Shell + depth values actually applied (derived in global, stored in advanced). */
    const effectiveShellSettings = computed((): ConsoleThemeSettings => {
        if (!isGlobalMode.value) {
            return settings.value;
        }
        const derived = deriveGlobalShellSettings(surfaceStyle.value);
        return { ...settings.value, ...derived };
    });

    const cssVars = computed((): Record<string, string> => {
        const vars: Record<string, string> = {};
        const isDark = systemStore.isDarkMode;
        const shell = effectiveShellSettings.value;

        if (isGlobalMode.value) {
            vars['--console-radius'] = normalizeRadiusPx(settings.value.console_button_radius, '8px');

            if (isCustomPreset.value) {
                const hex = isDark ? primaryDarkHex.value : primaryHex.value;
                vars['--console-primary'] = hex;
                const hsl = hexToHslComponents(hex);
                if (hsl) {
                    vars['--console-primary-hsl'] = hsl.hsl;
                    vars['--console-primary-foreground-hsl'] = hsl.foregroundHsl;
                }
            } else {
                const tokens = getPresetHslTokens(colorPreset.value, isDark);
                if (tokens) {
                    vars['--console-primary-hsl'] = tokens.primary;
                    vars['--console-primary-foreground-hsl'] = tokens.primaryForeground;
                }
            }

            if (surfaceStyle.value === 'glass') {
                const bg = buildConsoleGlassBackgroundImage({
                    preset: glassGradientPreset.value,
                    colorHex: glassGradientColorHex.value,
                    primaryHsl: vars['--console-primary-hsl'] ?? '238.9 77.1% 60.6%',
                    intensity: glassGradientIntensity.value,
                    angle: glassGradientAngle.value,
                });
                if (bg !== 'none') {
                    vars['--console-glass-bg-image'] = bg;
                }
            }
        } else {
            // Advanced: accent from brand hex only; preset selection is ignored.
            vars['--console-radius'] = normalizeRadiusPx(settings.value.console_button_radius, '8px');
            const hex = isDark ? primaryDarkHex.value : primaryHex.value;
            vars['--console-primary'] = hex;
            const hsl = hexToHslComponents(hex);
            if (hsl) {
                vars['--console-primary-hsl'] = hsl.hsl;
                vars['--console-primary-foreground-hsl'] = hsl.foregroundHsl;
            }
        }

        const sidebarStyle = shell.console_sidebar_style ?? 'glass';
        applySidebarVars(vars, sidebarStyle, isDark, primaryHex.value);
        applySidebarAccentVars(vars, settings.value.console_sidebar_accent);

        const navbarStyle = shell.console_navbar_style ?? 'glass';
        applyNavbarVars(vars, navbarStyle, isDark);

        applyDropdownVars(
            vars,
            String(shell.console_dropdown_style ?? 'standard'),
            Number(shell.console_popper_opacity ?? 85),
            isDark,
        );
        applyModalBackdropVars(vars, Number(shell.console_modal_backdrop_opacity ?? 50));
        applyCardStyleVars(vars, shell, isDark);
        applyIconWeightVars(vars, String(shell.console_icon_weight ?? 'regular'));
        applyTypographyVars(vars, shell);

        return vars;
    });

    const layoutAttrs = computed(() => {
        const attrs: Record<string, string> = {
            'data-console-theme-mode': themeMode.value,
        };
        if (isGlobalMode.value) {
            attrs['data-console-preset'] = colorPreset.value;
            attrs['data-console-surface'] = surfaceStyle.value;
            attrs['data-console-glass-gradient'] = glassGradientPreset.value;
            const derivedShell = effectiveShellSettings.value;
            attrs['data-console-button-style'] = normalizeConsoleButtonStyle(derivedShell.console_button_style);
            attrs['data-console-card-style'] = normalizeConsoleCardStyle(derivedShell.console_card_style);
            attrs['data-console-dropdown-style'] = normalizeConsoleDropdownStyle(derivedShell.console_dropdown_style);
            attrs['data-console-icon-weight'] = normalizeConsoleIconWeight(derivedShell.console_icon_weight);
        } else {
            const shell = effectiveShellSettings.value;
            attrs['data-console-surface'] = 'advanced';
            attrs['data-console-button-style'] = normalizeConsoleButtonStyle(shell.console_button_style);
            attrs['data-console-card-style'] = normalizeConsoleCardStyle(shell.console_card_style);
            attrs['data-console-dropdown-style'] = normalizeConsoleDropdownStyle(shell.console_dropdown_style);
            attrs['data-console-icon-weight'] = normalizeConsoleIconWeight(shell.console_icon_weight);
        }
        return attrs;
    });

    return {
        settings,
        loaded,
        load,
        applyDraft,
        themeMode,
        isGlobalMode,
        colorPreset,
        surfaceStyle,
        isCustomPreset,
        primaryHex,
        cssVars,
        layoutAttrs,
        effectiveShellSettings,
        glassGradientPreset,
        glassGradientIntensity,
        glassGradientAngle,
        glassGradientColorHex,
    };
}
