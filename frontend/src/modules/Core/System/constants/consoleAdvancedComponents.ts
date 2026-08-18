export type ConsoleButtonStyle = 'solid' | 'soft' | 'outline';
export type ConsoleCardStyle = 'flat' | 'soft' | 'elevated';
export type ConsoleDropdownStyle = 'minimal' | 'standard' | 'glass';
export type ConsoleIconWeight = 'light' | 'regular' | 'bold';

export const CONSOLE_BUTTON_STYLE_DEFAULT: ConsoleButtonStyle = 'solid';
export const CONSOLE_CARD_STYLE_DEFAULT: ConsoleCardStyle = 'soft';
export const CONSOLE_DROPDOWN_STYLE_DEFAULT: ConsoleDropdownStyle = 'standard';
export const CONSOLE_ICON_WEIGHT_DEFAULT: ConsoleIconWeight = 'regular';
export const CONSOLE_MODAL_BACKDROP_DEFAULT = 50;

export function normalizeConsoleButtonStyle(value: unknown): ConsoleButtonStyle {
    if (value === 'soft' || value === 'outline') return value;
    return CONSOLE_BUTTON_STYLE_DEFAULT;
}

export function normalizeConsoleCardStyle(value: unknown): ConsoleCardStyle {
    if (value === 'flat' || value === 'elevated') return value;
    return CONSOLE_CARD_STYLE_DEFAULT;
}

export function normalizeConsoleDropdownStyle(value: unknown): ConsoleDropdownStyle {
    if (value === 'minimal' || value === 'glass') return value;
    return CONSOLE_DROPDOWN_STYLE_DEFAULT;
}

export function normalizeConsoleIconWeight(value: unknown): ConsoleIconWeight {
    if (value === 'light' || value === 'bold') return value;
    return CONSOLE_ICON_WEIGHT_DEFAULT;
}

export function clampModalBackdropOpacity(value: unknown, fallback = CONSOLE_MODAL_BACKDROP_DEFAULT): number {
    const n = Number(value);
    if (!Number.isFinite(n)) return fallback;
    return Math.min(90, Math.max(0, Math.round(n)));
}
