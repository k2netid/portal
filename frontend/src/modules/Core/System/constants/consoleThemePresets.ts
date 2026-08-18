/**
 * Console admin color presets — synced with `styles/console-presets.css`.
 */
export type ConsoleColorPresetId =
    | 'monochrome_clean'
    | 'indigo_pro'
    | 'oceanic_clean'
    | 'emerald_fresh'
    | 'royal_violet'
    | 'sunset_coral'
    | 'midnight_cyan'
    | 'aurora_mint'
    | 'amber_pulse'
    | 'ruby_night'
    | 'custom';

export type ConsoleSurfaceStyle = 'flat' | 'glass';

export interface ConsolePresetHslPair {
    primary: string;
    primaryForeground: string;
}

export interface ConsoleThemePresetMeta {
    id: ConsoleColorPresetId;
    labelKey: string;
    descriptionKey: string;
    swatchHsl: string;
    isCustom?: boolean;
}

export const CONSOLE_COLOR_PRESET_CUSTOM: ConsoleColorPresetId = 'custom';
export const CONSOLE_SURFACE_GLASS: ConsoleSurfaceStyle = 'glass';
export const CONSOLE_SURFACE_FLAT: ConsoleSurfaceStyle = 'flat';

/** Inline HSL tokens (light / dark) — mirrors console-presets.css */
export const CONSOLE_PRESET_HSL: Record<
    Exclude<ConsoleColorPresetId, 'custom'>,
    { light: ConsolePresetHslPair; dark: ConsolePresetHslPair }
> = {
    monochrome_clean: {
        light: { primary: '0 0% 9%', primaryForeground: '0 0% 100%' },
        dark: { primary: '0 0% 100%', primaryForeground: '0 0% 0%' },
    },
    indigo_pro: {
        light: { primary: '238.9 77.1% 60.6%', primaryForeground: '0 0% 100%' },
        dark: { primary: '234 89% 74%', primaryForeground: '238.9 77.1% 12%' },
    },
    oceanic_clean: {
        light: { primary: '212 92% 45%', primaryForeground: '0 0% 100%' },
        dark: { primary: '206 100% 65%', primaryForeground: '212 92% 10%' },
    },
    emerald_fresh: {
        light: { primary: '158 75% 36%', primaryForeground: '0 0% 100%' },
        dark: { primary: '150 100% 55%', primaryForeground: '158 75% 10%' },
    },
    royal_violet: {
        light: { primary: '265 82% 52%', primaryForeground: '0 0% 100%' },
        dark: { primary: '265 92% 72%', primaryForeground: '265 82% 10%' },
    },
    sunset_coral: {
        light: { primary: '12 95% 58%', primaryForeground: '0 0% 100%' },
        dark: { primary: '12 100% 68%', primaryForeground: '12 95% 10%' },
    },
    midnight_cyan: {
        light: { primary: '192 95% 42%', primaryForeground: '0 0% 100%' },
        dark: { primary: '188 100% 55%', primaryForeground: '192 95% 10%' },
    },
    aurora_mint: {
        light: { primary: '162 82% 44%', primaryForeground: '0 0% 100%' },
        dark: { primary: '162 100% 58%', primaryForeground: '162 82% 10%' },
    },
    amber_pulse: {
        light: { primary: '38 92% 50%', primaryForeground: '48 96% 2%' },
        dark: { primary: '43 96% 58%', primaryForeground: '38 92% 10%' },
    },
    ruby_night: {
        light: { primary: '350 85% 50%', primaryForeground: '0 0% 100%' },
        dark: { primary: '350 100% 65%', primaryForeground: '350 85% 10%' },
    },
};

export const CONSOLE_THEME_PRESETS: ConsoleThemePresetMeta[] = [
    { id: 'monochrome_clean', labelKey: 'monochrome_clean', descriptionKey: 'monochrome_clean', swatchHsl: '0 0% 12%' },
    { id: 'indigo_pro', labelKey: 'indigo_pro', descriptionKey: 'indigo_pro', swatchHsl: '238.9 77.1% 60.6%' },
    { id: 'oceanic_clean', labelKey: 'oceanic_clean', descriptionKey: 'oceanic_clean', swatchHsl: '212 92% 45%' },
    { id: 'emerald_fresh', labelKey: 'emerald_fresh', descriptionKey: 'emerald_fresh', swatchHsl: '158 75% 36%' },
    { id: 'royal_violet', labelKey: 'royal_violet', descriptionKey: 'royal_violet', swatchHsl: '265 82% 52%' },
    { id: 'sunset_coral', labelKey: 'sunset_coral', descriptionKey: 'sunset_coral', swatchHsl: '12 95% 58%' },
    { id: 'midnight_cyan', labelKey: 'midnight_cyan', descriptionKey: 'midnight_cyan', swatchHsl: '192 95% 42%' },
    { id: 'aurora_mint', labelKey: 'aurora_mint', descriptionKey: 'aurora_mint', swatchHsl: '162 82% 44%' },
    { id: 'amber_pulse', labelKey: 'amber_pulse', descriptionKey: 'amber_pulse', swatchHsl: '38 92% 50%' },
    { id: 'ruby_night', labelKey: 'ruby_night', descriptionKey: 'ruby_night', swatchHsl: '350 85% 50%' },
    { id: 'custom', labelKey: 'custom', descriptionKey: 'custom', swatchHsl: '238.9 77.1% 60.6%', isCustom: true },
];

const PRESET_IDS = new Set(CONSOLE_THEME_PRESETS.map((p) => p.id));

export function normalizeConsoleColorPreset(value: unknown): ConsoleColorPresetId {
    const id = String(value ?? '').trim() as ConsoleColorPresetId;
    return PRESET_IDS.has(id) ? id : CONSOLE_COLOR_PRESET_CUSTOM;
}

export function normalizeConsoleSurfaceStyle(value: unknown): ConsoleSurfaceStyle {
    const v = String(value ?? '').trim();
    return v === CONSOLE_SURFACE_FLAT ? CONSOLE_SURFACE_FLAT : CONSOLE_SURFACE_GLASS;
}

export function getPresetHslTokens(
    preset: ConsoleColorPresetId,
    isDark: boolean,
): ConsolePresetHslPair | null {
    if (preset === 'custom') return null;
    return isDark ? CONSOLE_PRESET_HSL[preset].dark : CONSOLE_PRESET_HSL[preset].light;
}
