import type { SettingOption, SettingDefinition, ModuleSettings } from '@/types/builder';

/**
 * Effects Property Presets
 * Shadow, blur, opacity, and transform settings
 */

export const shadowOptions: SettingOption[] = [
    { label: 'None', value: 'none' },
    { label: 'Small', value: 'shadow-sm' },
    { label: 'Default', value: 'shadow' },
    { label: 'Medium', value: 'shadow-md' },
    { label: 'Large', value: 'shadow-lg' },
    { label: 'XL', value: 'shadow-xl' },
    { label: '2XL', value: 'shadow-2xl' },
    { label: 'Inner', value: 'shadow-inner' }
];

export const blendModeOptions: SettingOption[] = [
    { label: 'Normal', value: 'normal' },
    { label: 'Multiply', value: 'multiply' },
    { label: 'Screen', value: 'screen' },
    { label: 'Overlay', value: 'overlay' },
    { label: 'Darken', value: 'darken' },
    { label: 'Lighten', value: 'lighten' },
    { label: 'Color Dodge', value: 'color-dodge' },
    { label: 'Color Burn', value: 'color-burn' },
    { label: 'Hard Light', value: 'hard-light' },
    { label: 'Soft Light', value: 'soft-light' },
    { label: 'Difference', value: 'difference' },
    { label: 'Exclusion', value: 'exclusion' }
];

/**
 * Shadow settings
 */
export const shadowSettings: SettingDefinition[] = [
    { type: 'header', label: 'Shadow', tab: 'style' },
    {
        key: 'shadow',
        type: 'select',
        label: 'Box Shadow',
        options: shadowOptions,
        default: 'none',
        tab: 'style'
    },
    {
        key: 'shadowColor',
        type: 'color',
        label: 'Shadow Color',
        default: 'rgba(0, 0, 0, 0.1)',
        condition: (settings: ModuleSettings) => !!(settings.shadow && settings.shadow !== 'none'),
        tab: 'style'
    }
];

/**
 * Opacity settings
 */
export const opacitySettings: SettingDefinition[] = [
    {
        key: 'opacity',
        type: 'slider',
        label: 'Opacity',
        min: 0,
        max: 100,
        step: 5,
        default: 100,
        tab: 'style'
    }
];

/**
 * Filter settings (blur, brightness, contrast, etc)
 */
export const filterSettings: SettingDefinition[] = [
    { type: 'header', label: 'Filters', tab: 'style' },
    {
        key: 'blur',
        type: 'slider',
        label: 'Blur',
        min: 0,
        max: 20,
        step: 1,
        unit: 'px',
        default: 0,
        tab: 'style'
    },
    {
        key: 'brightness',
        type: 'slider',
        label: 'Brightness',
        min: 0,
        max: 200,
        step: 5,
        default: 100,
        tab: 'style'
    },
    {
        key: 'contrast',
        type: 'slider',
        label: 'Contrast',
        min: 0,
        max: 200,
        step: 5,
        default: 100,
        tab: 'style'
    },
    {
        key: 'saturate',
        type: 'slider',
        label: 'Saturation',
        min: 0,
        max: 200,
        step: 5,
        default: 100,
        tab: 'style'
    },
    {
        key: 'hueRotate',
        type: 'slider',
        label: 'Hue Rotate',
        min: 0,
        max: 360,
        step: 1,
        unit: 'deg',
        default: 0,
        tab: 'style'
    },
    {
        key: 'grayscale',
        type: 'slider',
        label: 'Grayscale',
        min: 0,
        max: 100,
        step: 5,
        default: 0,
        tab: 'style'
    }
];

/**
 * Transform settings
 */
export const transformSettings: SettingDefinition[] = [
    { type: 'header', label: 'Transform', tab: 'style' },
    {
        key: 'scale',
        type: 'slider',
        label: 'Scale',
        min: 50,
        max: 200,
        step: 5,
        default: 100,
        tab: 'style'
    },
    {
        key: 'rotate',
        type: 'slider',
        label: 'Rotate',
        min: -180,
        max: 180,
        step: 1,
        unit: 'deg',
        default: 0,
        tab: 'style'
    },
    {
        key: 'skewX',
        type: 'slider',
        label: 'Skew X',
        min: -45,
        max: 45,
        step: 1,
        unit: 'deg',
        default: 0,
        tab: 'style'
    },
    {
        key: 'skewY',
        type: 'slider',
        label: 'Skew Y',
        min: -45,
        max: 45,
        step: 1,
        unit: 'deg',
        default: 0,
        tab: 'style'
    },
    {
        key: 'translateX',
        type: 'slider',
        label: 'Translate X',
        min: -200,
        max: 200,
        step: 1,
        unit: 'px',
        default: 0,
        tab: 'style'
    },
    {
        key: 'translateY',
        type: 'slider',
        label: 'Translate Y',
        min: -200,
        max: 200,
        step: 1,
        unit: 'px',
        default: 0,
        tab: 'style'
    }
];

/**
 * Complete effects settings
 */
export const effectsSettings: SettingDefinition[] = [
    ...shadowSettings,
    ...opacitySettings,
    ...filterSettings,
    ...transformSettings
];

/**
 * Effects defaults
 */
export const effectsDefaults: ModuleSettings = {
    shadow: 'none',
    shadowColor: 'rgba(0, 0, 0, 0.1)',
    opacity: 100,
    blur: 0,
    brightness: 100,
    contrast: 100,
    saturate: 100,
    hueRotate: 0,
    grayscale: 0,
    scale: 100,
    rotate: 0,
    skewX: 0,
    skewY: 0,
    translateX: 0,
    translateY: 0
};

/**
 * Generate CSS filter string
 */
export const getFilterStyle = (settings: ModuleSettings) => {
    const filters: string[] = [];

    if (settings.blur) filters.push(`blur(${settings.blur}px)`);
    if (settings.brightness !== 100) filters.push(`brightness(${settings.brightness}%)`);
    if (settings.contrast !== 100) filters.push(`contrast(${settings.contrast}%)`);
    if (settings.saturate !== 100) filters.push(`saturate(${settings.saturate}%)`);
    if (settings.hueRotate) filters.push(`hue-rotate(${settings.hueRotate}deg)`);
    if (settings.grayscale) filters.push(`grayscale(${settings.grayscale}%)`);

    return filters.length > 0 ? filters.join(' ') : 'none';
};

/**
 * Generate CSS transform string
 */
export const getTransformStyle = (settings: ModuleSettings) => {
    const transforms: string[] = [];

    if ((settings.scale as number) !== 100) transforms.push(`scale(${(settings.scale as number) / 100})`);
    if (settings.rotate) transforms.push(`rotate(${settings.rotate}deg)`);
    if (settings.skewX) transforms.push(`skewX(${settings.skewX}deg)`);
    if (settings.skewY) transforms.push(`skewY(${settings.skewY}deg)`);
    if (settings.translateX) transforms.push(`translateX(${settings.translateX}px)`);
    if (settings.translateY) transforms.push(`translateY(${settings.translateY}px)`);

    return transforms.length > 0 ? transforms.join(' ') : 'none';
};
