/**
 * Shared color utilities for theme customizer and console branding (Tailwind HSL tokens).
 */

export interface HslComponents {
    hsl: string;
    foregroundHsl: string;
}

/**
 * Parse hex (#RGB or #RRGGBB) into space-separated HSL components for CSS variables.
 */
export function hexToHslString(hex: string): string | null {
    const parts = hexToHslComponents(hex);
    return parts?.hsl ?? null;
}

/**
 * Parse hex and compute accessible foreground (WCAG lightness heuristic).
 */
export function hexToHslComponents(hex: string): HslComponents | null {
    if (!hex || typeof hex !== 'string' || !hex.startsWith('#')) {
        return null;
    }

    let r: number;
    let g: number;
    let b: number;
    if (hex.length === 4) {
        r = parseInt('0x' + hex[1] + hex[1], 16);
        g = parseInt('0x' + hex[2] + hex[2], 16);
        b = parseInt('0x' + hex[3] + hex[3], 16);
    } else if (hex.length === 7) {
        r = parseInt('0x' + hex[1] + hex[2], 16);
        g = parseInt('0x' + hex[3] + hex[4], 16);
        b = parseInt('0x' + hex[5] + hex[6], 16);
    } else {
        return null;
    }

    r /= 255;
    g /= 255;
    b /= 255;

    const cmin = Math.min(r, g, b);
    const cmax = Math.max(r, g, b);
    const delta = cmax - cmin;
    let h: number;
    let s = 0;
    let l = (cmax + cmin) / 2;

    if (delta === 0) {
        h = 0;
        s = 0;
    } else if (cmax === r) {
        h = ((g - b) / delta) % 6;
    } else if (cmax === g) {
        h = (b - r) / delta + 2;
    } else {
        h = (r - g) / delta + 4;
    }

    h = Math.round(h * 60);
    if (h < 0) {
        h += 360;
    }

    if (delta !== 0) {
        l = (cmax + cmin) / 2;
        s = delta / (1 - Math.abs(2 * l - 1));
    }
    s = parseFloat((s * 100).toFixed(1));
    l = parseFloat((l * 100).toFixed(1));

    const hsl = `${h} ${s}% ${l}%`;
    const foregroundHsl = l > 60 ? '0 0% 0%' : '0 0% 100%';

    return { hsl, foregroundHsl };
}

/** Normalize console_button_radius from DB (int or string) to CSS length. */
export function normalizeRadiusPx(value: unknown, fallback = '8px'): string {
    if (typeof value === 'number' && Number.isFinite(value)) {
        return `${value}px`;
    }
    if (typeof value === 'string' && value.trim() !== '') {
        const trimmed = value.trim();
        return trimmed.endsWith('px') ? trimmed : `${trimmed}px`;
    }
    return fallback;
}

/**
 * Calculates the relative luminance of a hex color.
 * Based on W3C relative luminance definition.
 */
export function getRelativeLuminance(hex: string): number {
    if (!hex || typeof hex !== 'string') return 0;
    let cleanHex = hex.replace('#', '');
    if (cleanHex.length === 3) {
        cleanHex = cleanHex.split('').map(c => c + c).join('');
    }
    if (cleanHex.length !== 6) return 0;

    const r = parseInt(cleanHex.substring(0, 2), 16) / 255;
    const g = parseInt(cleanHex.substring(2, 4), 16) / 255;
    const b = parseInt(cleanHex.substring(4, 6), 16) / 255;

    const transform = (val: number) => {
        return val <= 0.03928 ? val / 12.92 : Math.pow((val + 0.055) / 1.055, 2.4);
    };

    return 0.2126 * transform(r) + 0.7152 * transform(g) + 0.0722 * transform(b);
}

/**
 * Calculates the contrast ratio between two hex colors.
 * Contrast ratio is (L1 + 0.05) / (L2 + 0.05) where L1 is lighter relative luminance.
 */
export function calculateContrastRatio(hex1: string, hex2: string): number {
    const l1 = getRelativeLuminance(hex1);
    const l2 = getRelativeLuminance(hex2);
    const brightest = Math.max(l1, l2);
    const darkest = Math.min(l1, l2);
    return parseFloat(((brightest + 0.05) / (darkest + 0.05)).toFixed(2));
}

