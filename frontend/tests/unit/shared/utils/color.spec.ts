import { describe, it, expect } from 'vitest';
import {
    hexToHslString,
    hexToHslComponents,
    normalizeRadiusPx,
    getRelativeLuminance,
    calculateContrastRatio,
} from '@/shared/utils/color';

describe('color utils', () => {
    describe('hexToHsl and hexToHslComponents', () => {
        it('converts 6-digit hex color to HSL components with accessible foreground', () => {
            const res = hexToHslComponents('#ffffff');
            expect(res?.hsl).toBe('0 0% 100%');
            expect(res?.foregroundHsl).toBe('0 0% 0%'); // Black text on white
            expect(hexToHslString('#ffffff')).toBe('0 0% 100%');

            const dark = hexToHslComponents('#000000');
            expect(dark?.hsl).toBe('0 0% 0%');
            expect(dark?.foregroundHsl).toBe('0 0% 100%'); // White text on black
        });

        it('supports 3-digit shorthand hex', () => {
            const res = hexToHslComponents('#f00');
            expect(res?.hsl).toBe('0 100% 50%');
        });

        it('converts green and blue dominant colors correctly', () => {
            const green = hexToHslComponents('#00ff00');
            expect(green?.hsl).toBe('120 100% 50%');

            const blue = hexToHslComponents('#0000ff');
            expect(blue?.hsl).toBe('240 100% 50%');
        });

        it('returns null for invalid hex strings', () => {
            expect(hexToHslComponents('red')).toBeNull();
            expect(hexToHslComponents('#12')).toBeNull();
            expect(hexToHslComponents('#12345678')).toBeNull();
            expect(hexToHslString('invalid')).toBeNull();
        });
    });
    describe('normalizeRadiusPx', () => {
        it('normalizes numeric values to px string', () => {
            expect(normalizeRadiusPx(8)).toBe('8px');
            expect(normalizeRadiusPx(0)).toBe('0px');
            expect(normalizeRadiusPx(16.5)).toBe('16.5px');
        });

        it('normalizes string values', () => {
            expect(normalizeRadiusPx('8px')).toBe('8px');
            expect(normalizeRadiusPx('12')).toBe('12px');
            expect(normalizeRadiusPx(' 4px ')).toBe('4px');
        });

        it('falls back when invalid or empty', () => {
            expect(normalizeRadiusPx('')).toBe('8px');
            expect(normalizeRadiusPx(null)).toBe('8px');
            expect(normalizeRadiusPx(undefined, '12px')).toBe('12px');
            expect(normalizeRadiusPx(NaN)).toBe('8px');
        });
    });

    describe('getRelativeLuminance', () => {
        it('calculates relative luminance for 6-digit hex colors', () => {
            expect(getRelativeLuminance('#ffffff')).toBeCloseTo(1.0, 2);
            expect(getRelativeLuminance('#000000')).toBeCloseTo(0.0, 2);
            expect(getRelativeLuminance('#808080')).toBeGreaterThan(0);
        });

        it('supports 3-digit hex shorthand', () => {
            expect(getRelativeLuminance('#fff')).toBeCloseTo(1.0, 2);
            expect(getRelativeLuminance('#000')).toBeCloseTo(0.0, 2);
        });

        it('returns 0 for invalid inputs', () => {
            expect(getRelativeLuminance('')).toBe(0);
            expect(getRelativeLuminance('not-a-color')).toBe(0);
            expect(getRelativeLuminance(null as any)).toBe(0);
        });
    });

    describe('calculateContrastRatio', () => {
        it('calculates contrast ratio between black and white', () => {
            const ratio = calculateContrastRatio('#ffffff', '#000000');
            expect(ratio).toBe(21);
        });

        it('calculates identical colors ratio as 1', () => {
            const ratio = calculateContrastRatio('#ffffff', '#ffffff');
            expect(ratio).toBe(1);
        });

        it('is commutative regardless of order', () => {
            const r1 = calculateContrastRatio('#0284c7', '#ffffff');
            const r2 = calculateContrastRatio('#ffffff', '#0284c7');
            expect(r1).toBe(r2);
        });
    });
});
