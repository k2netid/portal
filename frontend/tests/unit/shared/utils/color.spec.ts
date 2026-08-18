import { describe, expect, it } from 'vitest';
import { hexToHslComponents, hexToHslString, normalizeRadiusPx } from '@/shared/utils/color';

describe('color utils', () => {
    it('converts hex to hsl string', () => {
        expect(hexToHslString('#4f46e5')).toMatch(/^\d+ [\d.]+% [\d.]+%$/);
    });

    it('picks dark foreground on light brand colors', () => {
        const res = hexToHslComponents('#ffffff');
        expect(res?.foregroundHsl).toBe('0 0% 0%');
    });

    it('normalizes radius from integer', () => {
        expect(normalizeRadiusPx(12)).toBe('12px');
    });
});
