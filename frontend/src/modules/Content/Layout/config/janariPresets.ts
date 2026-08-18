export const JANARI_PRESETS = {
    monochrome_clean: { light: '#000000', dark: '#ffffff', hslLight: '0 0% 0%', hslDark: '0 0% 100%' },
    oceanic_clean: { light: '#0066ff', dark: '#4db1ff', hslLight: '217 100% 50%', hslDark: '206 100% 65%' },
    emerald_fresh: { light: '#00b871', dark: '#1aff8a', hslLight: '155 100% 36%', hslDark: '150 100% 55%' },
    royal_violet: { light: '#9333ea', dark: '#c084fc', hslLight: '270 100% 55%', hslDark: '270 96% 75%' },
    sunset_coral: { light: '#ff4444', dark: '#ff6f5c', hslLight: '0 100% 67%', hslDark: '12 100% 68%' },
    midnight_cyan: { light: '#00d4ff', dark: '#1affef', hslLight: '190 100% 50%', hslDark: '188 100% 55%' },
    forest_earth: { light: '#22c55e', dark: '#63f191', hslLight: '142 71% 45%', hslDark: '142 85% 60%' },
    ruby_night: { light: '#ff1744', dark: '#ff4d6a', hslLight: '0 100% 55%', hslDark: '350 100% 65%' },
    aurora_mint: { light: '#14cc96', dark: '#60f9cc', hslLight: '162 82% 44%', hslDark: '162 92% 64%' },
    slate_indigo: { light: '#5b5bff', dark: '#8aa3ff', hslLight: '240 100% 68%', hslDark: '235 95% 75%' },
    arctic_blue: { light: '#18b6f6', dark: '#5cbdff', hslLight: '196 94% 52%', hslDark: '202 100% 68%' },
    sand_stone: { light: '#f59e0b', dark: '#fbbf24', hslLight: '38 92% 50%', hslDark: '38 96% 65%' },
} as const;

export type JanariPresetKey = keyof typeof JANARI_PRESETS;
