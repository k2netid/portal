type Intensity = 'subtle' | 'normal' | 'dramatic';

export function coerceThemeFlag(value: unknown, fallback = true): boolean {
    if (value === false || value === 0 || value === '0' || value === 'false' || value === 'off') return false;
    if (value === true || value === 1 || value === '1' || value === 'true' || value === 'on') return true;
    if (value == null || value === '') return fallback;
    return Boolean(value);
}

export function motionIntensityScale(raw: unknown): { distance: number; duration: number } {
    const key = String(raw || 'normal') as Intensity;
    if (key === 'subtle') return { distance: 0.55, duration: 0.9 };
    if (key === 'dramatic') return { distance: 1.45, duration: 1.15 };
    return { distance: 1, duration: 1 };
}
