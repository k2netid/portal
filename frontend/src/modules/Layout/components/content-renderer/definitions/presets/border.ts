import type { SettingOption, SettingDefinition, ModuleSettings } from '@/types/builder';

/**
 * Border Property Presets
 * Border width, style, color, and radius settings
 */

export const borderStyleOptions: SettingOption[] = [
    { label: 'None', value: 'none' },
    { label: 'Solid', value: 'solid' },
    { label: 'Dashed', value: 'dashed' },
    { label: 'Dotted', value: 'dotted' },
    { label: 'Double', value: 'double' }
];

export const borderRadiusOptions: SettingOption[] = [
    { label: 'None', value: '0' },
    { label: 'Sm', value: '0.25rem' },
    { label: 'Md', value: '0.5rem' },
    { label: 'Lg', value: '1rem' },
    { label: 'XL', value: '1.5rem' },
    { label: '2XL', value: '2rem' },
    { label: 'Full', value: '9999px' }
];

export const borderRadiusPresets: SettingOption[] = [
    { label: 'None', value: 'rounded-none' },
    { label: 'Sm', value: 'rounded-sm' },
    { label: 'Md', value: 'rounded-md' },
    { label: 'Lg', value: 'rounded-lg' },
    { label: 'XL', value: 'rounded-xl' },
    { label: '2XL', value: 'rounded-2xl' },
    { label: 'Full', value: 'rounded-full' }
];

/**
 * Complete border settings
 */
export const borderSettings: SettingDefinition[] = [
    { type: 'header', label: 'Border', tab: 'style' },
    {
        key: 'borderStyle',
        type: 'select',
        label: 'Border Style',
        options: borderStyleOptions,
        default: 'none',
        tab: 'style'
    },
    {
        key: 'borderWidth',
        type: 'slider',
        label: 'Border Width',
        min: 0,
        max: 10,
        step: 1,
        unit: 'px',
        default: 1,
        condition: (settings: ModuleSettings) => settings.borderStyle !== 'none',
        tab: 'style'
    },
    {
        key: 'borderColor',
        type: 'color',
        label: 'Border Color',
        default: '#e5e7eb',
        condition: (settings: ModuleSettings) => settings.borderStyle !== 'none',
        tab: 'style'
    },
    {
        key: 'borderRadius',
        type: 'slider',
        label: 'Border Radius',
        min: 0,
        max: 100,
        step: 1,
        unit: 'px',
        default: 0,
        tab: 'style'
    }
];

/**
 * Simple radius-only setting (toggle group)
 */
export const simpleRadiusSettings: SettingDefinition[] = [
    {
        key: 'radius',
        type: 'toggle_group',
        label: 'Border Radius',
        options: borderRadiusPresets,
        default: 'rounded-none',
        tab: 'style'
    }
];

/**
 * Border defaults
 */
export const borderDefaults: ModuleSettings = {
    borderStyle: 'none',
    borderWidth: 1,
    borderColor: '#e5e7eb',
    borderRadius: 0,
    radius: 'rounded-none'
};

/**
 * Generate CSS border style object
 */
export const getBorderStyle = (settings: ModuleSettings) => {
    const style: Record<string, string | number | undefined> = {};

    if (settings.borderStyle && settings.borderStyle !== 'none') {
        style.borderStyle = settings.borderStyle as string;
        style.borderWidth = `${(settings.borderWidth as number) || 1}px`;
        style.borderColor = (settings.borderColor as string) || '#e5e7eb';
    }

    if (settings.borderRadius) {
        style.borderRadius = typeof settings.borderRadius === 'number'
            ? `${settings.borderRadius}px`
            : settings.borderRadius as string;
    }

    return style;
};
