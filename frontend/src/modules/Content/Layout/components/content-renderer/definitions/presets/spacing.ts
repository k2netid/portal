import type { SettingOption, SettingDefinition, ModuleSettings } from '@/types/builder';

/**
 * Spacing Property Presets
 * Padding and margin settings with responsive support
 */

/**
 * Preset spacing options (Tailwind-based values)
 */
export const spacingOptions: SettingOption[] = [
    { label: 'None', value: '0' },
    { label: 'XS', value: '0.25rem' },
    { label: 'Sm', value: '0.5rem' },
    { label: 'Md', value: '1rem' },
    { label: 'Lg', value: '1.5rem' },
    { label: 'XL', value: '2rem' },
    { label: '2XL', value: '3rem' },
    { label: '3XL', value: '4rem' },
    { label: '4XL', value: '6rem' }
];

/**
 * Box model settings - for padding property field
 */
export const paddingSettings: SettingDefinition[] = [
    { type: 'header', label: 'Padding', tab: 'style' },
    {
        key: 'padding',
        type: 'box_model',
        mode: 'padding',
        label: 'Padding',
        default: { top: '0', right: '0', bottom: '0', left: '0' },
        responsive: true,
        tab: 'style'
    }
];

/**
 * Box model settings - for margin property field
 */
export const marginSettings: SettingDefinition[] = [
    { type: 'header', label: 'Margin', tab: 'style' },
    {
        key: 'margin',
        type: 'box_model',
        mode: 'margin',
        label: 'Margin',
        default: { top: '0', right: '0', bottom: '0', left: '0' },
        responsive: true,
        tab: 'style'
    }
];

/**
 * Full spacing settings (padding + margin)
 */
export const spacingSettings: SettingDefinition[] = [
    ...paddingSettings,
    ...marginSettings
];

/**
 * Simple padding presets (toggle group style)
 */
export const simplePaddingSettings: SettingDefinition[] = [
    {
        key: 'paddingY',
        type: 'toggle_group',
        label: 'Vertical Padding',
        options: [
            { label: 'None', value: 'py-0' },
            { label: 'Sm', value: 'py-4' },
            { label: 'Md', value: 'py-8' },
            { label: 'Lg', value: 'py-16' },
            { label: 'XL', value: 'py-24' }
        ],
        default: 'py-8',
        responsive: true,
        tab: 'style'
    },
    {
        key: 'paddingX',
        type: 'toggle_group',
        label: 'Horizontal Padding',
        options: [
            { label: 'None', value: 'px-0' },
            { label: 'Sm', value: 'px-4' },
            { label: 'Md', value: 'px-6' },
            { label: 'Lg', value: 'px-8' },
            { label: 'XL', value: 'px-12' }
        ],
        default: 'px-6',
        responsive: true,
        tab: 'style'
    }
];

/**
 * Gap settings for flex/grid containers
 */
export const gapSettings: SettingDefinition[] = [
    {
        key: 'gap',
        type: 'slider',
        label: 'Gap',
        min: 0,
        max: 80,
        step: 4,
        unit: 'px',
        default: 16,
        responsive: true,
        tab: 'style'
    }
];

/**
 * Spacing defaults
 */
export const spacingDefaults: ModuleSettings = {
    padding: { top: '0', right: '0', bottom: '0', left: '0' },
    margin: { top: '0', right: '0', bottom: '0', left: '0' },
    paddingY: 'py-8',
    paddingX: 'px-6',
    gap: 16
};
