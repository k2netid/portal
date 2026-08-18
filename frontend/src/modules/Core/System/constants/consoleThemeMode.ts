import type { ConsoleSurfaceStyle } from '@/modules/Core/System/constants/consoleThemePresets';

/** How console appearance settings are applied — mutually exclusive at runtime. */
export type ConsoleThemeMode = 'global' | 'advanced';

export const CONSOLE_THEME_MODE_GLOBAL: ConsoleThemeMode = 'global';
export const CONSOLE_THEME_MODE_ADVANCED: ConsoleThemeMode = 'advanced';
export const CONSOLE_THEME_MODE_DEFAULT: ConsoleThemeMode = CONSOLE_THEME_MODE_GLOBAL;

/** Semantic tokens + surfaces — one coherent preset drives the shell. */
export const CONSOLE_THEME_GLOBAL_KEYS = [
    'console_color_preset',
    'console_brand_primary',
    'console_brand_primary_dark',
    'console_button_radius',
    'console_surface_style',
    'console_glass_gradient_preset',
    'console_glass_gradient_color',
    'console_glass_gradient_intensity',
    'console_glass_gradient_angle',
] as const;

/** Per-region / component chrome — sidebar, navbar, poppers, typography, depth. */
export const CONSOLE_THEME_ADVANCED_KEYS = [
    'console_sidebar_style',
    'console_navbar_style',
    'console_button_style',
    'console_card_style',
    'console_popper_opacity',
    'console_modal_backdrop_opacity',
    'console_dropdown_style',
    'console_icon_weight',
    'console_font_primary',
    'console_font_mono',
    'console_shadow_elevation',
    'console_border_style',
] as const;

export type ConsoleThemeGlobalKey = (typeof CONSOLE_THEME_GLOBAL_KEYS)[number];
export type ConsoleThemeAdvancedKey = (typeof CONSOLE_THEME_ADVANCED_KEYS)[number];

export interface ConsoleDerivedShellSettings {
    console_sidebar_style: string;
    console_navbar_style: string;
    console_button_style: string;
    console_card_style: string;
    console_popper_opacity: number;
    console_modal_backdrop_opacity: number;
    console_dropdown_style: string;
    console_icon_weight: string;
    console_font_primary: string;
    console_font_mono: string;
    console_shadow_elevation: string;
    console_border_style: string;
}

export function normalizeConsoleThemeMode(value: unknown): ConsoleThemeMode {
    return value === CONSOLE_THEME_MODE_ADVANCED
        ? CONSOLE_THEME_MODE_ADVANCED
        : CONSOLE_THEME_MODE_GLOBAL;
}

/**
 * Coherent shell defaults when global mode is active.
 * Derived from surface style so preset + glass/flat stay visually consistent.
 */
export function deriveGlobalShellSettings(surface: ConsoleSurfaceStyle): ConsoleDerivedShellSettings {
    if (surface === 'flat') {
        return {
            console_sidebar_style: 'clean',
            console_navbar_style: 'bordered',
            console_button_style: 'solid',
            console_card_style: 'flat',
            console_popper_opacity: 92,
            console_modal_backdrop_opacity: 45,
            console_dropdown_style: 'minimal',
            console_icon_weight: 'regular',
            console_font_primary: 'system',
            console_font_mono: 'system',
            console_shadow_elevation: 'flat',
            console_border_style: 'subtle',
        };
    }
    return {
        console_sidebar_style: 'glass',
        console_navbar_style: 'glass',
        console_button_style: 'solid',
        console_card_style: 'soft',
        console_popper_opacity: 85,
        console_modal_backdrop_opacity: 55,
        console_dropdown_style: 'glass',
        console_icon_weight: 'regular',
        console_font_primary: 'system',
        console_font_mono: 'system',
        console_shadow_elevation: 'soft',
        console_border_style: 'subtle',
    };
}
