import { describe, expect, it } from 'vitest';
import { coerceThemeFlag, motionIntensityScale } from '@/modules/Layout/composables/useThemeMotionSettings';

describe('theme motion settings', () => {
    it('coerces customizer boolean flags', () => {
        expect(coerceThemeFlag(true)).toBe(true);
        expect(coerceThemeFlag(false)).toBe(false);
        expect(coerceThemeFlag('false')).toBe(false);
        expect(coerceThemeFlag('off')).toBe(false);
        expect(coerceThemeFlag('', true)).toBe(true);
        expect(coerceThemeFlag(null, true)).toBe(true);
    });

    it('scales distance and duration by intensity', () => {
        expect(motionIntensityScale('subtle').distance).toBeLessThan(1);
        expect(motionIntensityScale('normal')).toEqual({ distance: 1, duration: 1 });
        expect(motionIntensityScale('dramatic').distance).toBeGreaterThan(1);
    });
});
