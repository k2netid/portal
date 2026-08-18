import { hexToHslComponents } from '@/shared/utils/color';

export type ConsoleGlassGradientPresetId =
    | 'none'
    | 'subtle'
    | 'balanced'
    | 'vivid'
    | 'aurora'
    | 'sunset'
    | 'corner_glow'
    | 'custom';

export interface ConsoleGlassGradientPresetMeta {
    id: ConsoleGlassGradientPresetId;
    labelKey: string;
    descriptionKey: string;
    isCustom?: boolean;
}

export const CONSOLE_GLASS_GRADIENT_CUSTOM: ConsoleGlassGradientPresetId = 'custom';
export const CONSOLE_GLASS_GRADIENT_DEFAULT: ConsoleGlassGradientPresetId = 'balanced';

export const CONSOLE_GLASS_GRADIENT_PRESETS: ConsoleGlassGradientPresetMeta[] = [
    { id: 'none', labelKey: 'none', descriptionKey: 'none' },
    { id: 'subtle', labelKey: 'subtle', descriptionKey: 'subtle' },
    { id: 'balanced', labelKey: 'balanced', descriptionKey: 'balanced' },
    { id: 'vivid', labelKey: 'vivid', descriptionKey: 'vivid' },
    { id: 'aurora', labelKey: 'aurora', descriptionKey: 'aurora' },
    { id: 'sunset', labelKey: 'sunset', descriptionKey: 'sunset' },
    { id: 'corner_glow', labelKey: 'cornerGlow', descriptionKey: 'cornerGlow' },
    { id: 'custom', labelKey: 'custom', descriptionKey: 'custom', isCustom: true },
];

const VALID = new Set(CONSOLE_GLASS_GRADIENT_PRESETS.map((p) => p.id));

export function normalizeConsoleGlassGradientPreset(value: unknown): ConsoleGlassGradientPresetId {
    const id = String(value ?? CONSOLE_GLASS_GRADIENT_DEFAULT);
    return VALID.has(id as ConsoleGlassGradientPresetId)
        ? (id as ConsoleGlassGradientPresetId)
        : CONSOLE_GLASS_GRADIENT_DEFAULT;
}

export function clampGlassIntensity(value: unknown, fallback = 55): number {
    const n = Number(value);
    if (!Number.isFinite(n)) return fallback;
    return Math.min(100, Math.max(0, Math.round(n)));
}

export function clampGlassAngle(value: unknown, fallback = 135): number {
    const n = Number(value);
    if (!Number.isFinite(n)) return fallback;
    return ((Math.round(n) % 360) + 360) % 360;
}

export interface BuildGlassGradientOptions {
    preset: ConsoleGlassGradientPresetId;
    colorHex: string;
    primaryHsl: string;
    intensity: number;
    angle: number;
}

function hslFromColor(colorHex: string, primaryHsl: string): string {
    const fromHex = hexToHslComponents(colorHex);
    return fromHex?.hsl ?? primaryHsl;
}

function scaleOpacity(base: number, intensity: number, max = 0.42): number {
    const factor = 0.55 + (intensity / 100) * 0.9;
    return Math.min(max, base * factor);
}

function focalFromAngle(angle: number): { x: number; y: number } {
    const rad = ((angle - 90) * Math.PI) / 180;
    return {
        x: Math.round(50 + Math.cos(rad) * 38),
        y: Math.round(50 + Math.sin(rad) * 38),
    };
}

function radial(hsl: string, opacity: number, x: string, y: string, w: string, h: string): string {
    return `radial-gradient(ellipse ${w} ${h} at ${x} ${y}, hsl(${hsl} / ${opacity.toFixed(3)}), transparent 58%)`;
}

export function buildConsoleGlassBackgroundImage(options: BuildGlassGradientOptions): string | 'none' {
    const { preset, intensity, angle } = options;
    const hsl = hslFromColor(options.colorHex, options.primaryHsl);

    if (preset === 'none') return 'none';

    if (preset === 'custom') {
        const op1 = scaleOpacity(0.22, intensity, 0.48);
        const op2 = scaleOpacity(0.12, intensity, 0.28);
        const size = Math.round(48 + (intensity / 100) * 52);
        const { x, y } = focalFromAngle(angle);
        return [
            radial(hsl, op1, `${x}%`, `${y}%`, `${size}%`, `${Math.round(size * 0.68)}%`),
            radial(hsl, op2, `${100 - x}%`, `${100 - y}%`, `${Math.round(size * 0.75)}%`, `${Math.round(size * 0.5)}%`),
            `linear-gradient(${angle}deg, hsl(${hsl} / ${(op1 * 0.35).toFixed(3)}), transparent 72%)`,
        ].join(', ');
    }

    const presets: Record<Exclude<ConsoleGlassGradientPresetId, 'none' | 'custom'>, () => string> = {
        subtle: () => [
            radial(hsl, scaleOpacity(0.08, intensity), '10%', '-10%', '88%', '58%'),
            radial(hsl, scaleOpacity(0.04, intensity), '100%', '0%', '68%', '48%'),
        ].join(', '),
        balanced: () => [
            radial(hsl, scaleOpacity(0.2, intensity), '8%', '-12%', '92%', '62%'),
            radial(hsl, scaleOpacity(0.12, intensity), '95%', '5%', '72%', '52%'),
        ].join(', '),
        vivid: () => [
            radial(hsl, scaleOpacity(0.32, intensity, 0.5), '5%', '-15%', '100%', '68%'),
            radial(hsl, scaleOpacity(0.2, intensity, 0.38), '100%', '0%', '80%', '55%'),
            radial(hsl, scaleOpacity(0.1, intensity), '50%', '100%', '60%', '40%'),
        ].join(', '),
        aurora: () => {
            const accent = shiftHue(hsl, 72);
            const accent2 = shiftHue(hsl, 140);
            return [
                radial(hsl, scaleOpacity(0.24, intensity), '0%', '0%', '70%', '55%'),
                radial(accent, scaleOpacity(0.18, intensity), '100%', '0%', '65%', '50%'),
                radial(accent2, scaleOpacity(0.1, intensity), '50%', '100%', '55%', '45%'),
            ].join(', ');
        },
        sunset: () => {
            const warm = shiftHue(hsl, -25, 8);
            return [
                radial(hsl, scaleOpacity(0.26, intensity), '15%', '-5%', '85%', '60%'),
                radial(warm, scaleOpacity(0.16, intensity), '100%', '20%', '70%', '50%'),
            ].join(', ');
        },
        corner_glow: () => [
            radial(hsl, scaleOpacity(0.28, intensity), '0%', '0%', '75%', '58%'),
            radial(hsl, scaleOpacity(0.2, intensity), '100%', '100%', '70%', '52%'),
        ].join(', '),
    };

    const builder = presets[preset as keyof typeof presets];
    return builder ? builder() : presets.balanced();
}

function shiftHue(hsl: string, degrees: number, saturateBoost = 0): string {
    const parts = hsl.trim().split(/\s+/);
    if (parts.length < 3) return hsl;
    const h = Number.parseFloat(parts[0] ?? '0');
    const s = parts[1] ?? '50%';
    const l = parts[2] ?? '50%';
    const newH = ((h + degrees) % 360 + 360) % 360;
    if (saturateBoost === 0) return `${newH} ${s} ${l}`;
    const sNum = Number.parseFloat(s);
    return `${newH} ${Math.min(100, sNum + saturateBoost)}% ${l}`;
}

export function glassGradientSwatchStyle(
    preset: ConsoleGlassGradientPresetId,
    primaryHsl: string,
    colorHex: string,
): Record<string, string> {
    if (preset === 'none') return { background: 'hsl(var(--muted))' };
    const bg = buildConsoleGlassBackgroundImage({
        preset: preset === 'custom' ? 'balanced' : preset,
        colorHex,
        primaryHsl,
        intensity: preset === 'subtle' ? 40 : preset === 'vivid' ? 75 : 58,
        angle: 135,
    });
    const style: Record<string, string> = { backgroundColor: 'hsl(var(--muted))' };
    if (bg !== 'none') style.backgroundImage = bg;
    return style;
}
