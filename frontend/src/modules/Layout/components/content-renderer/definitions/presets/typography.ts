import type { SettingOption, SettingDefinition, ModuleSettings } from '@/types/builder';
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import AlignJustify from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-center.js';

/**
 * Typography Property Presets
 * Standard typography settings for text-based components
 */

export const fontFamilyOptions: SettingOption[] = [
    { label: 'System', value: 'inherit' },
    { label: 'Inter', value: "'Inter', sans-serif" },
    { label: 'Roboto', value: "'Roboto', sans-serif" },
    { label: 'Open Sans', value: "'Open Sans', sans-serif" },
    { label: 'Poppins', value: "'Poppins', sans-serif" },
    { label: 'Montserrat', value: "'Montserrat', sans-serif" },
    { label: 'Lato', value: "'Lato', sans-serif" },
    { label: 'Playfair Display', value: "'Playfair Display', serif" },
    { label: 'Merriweather', value: "'Merriweather', serif" },
    { label: 'Georgia', value: "Georgia, serif" },
    { label: 'Monospace', value: "'JetBrains Mono', monospace" }
];

export const fontWeightOptions: SettingOption[] = [
    { label: 'Light', value: '300' },
    { label: 'Normal', value: '400' },
    { label: 'Medium', value: '500' },
    { label: 'Semi', value: '600' },
    { label: 'Bold', value: '700' },
    { label: 'Black', value: '900' }
];

export const textTransformOptions: SettingOption[] = [
    { label: 'None', value: 'none' },
    { label: 'Uppercase', value: 'uppercase' },
    { label: 'Lowercase', value: 'lowercase' },
    { label: 'Capitalize', value: 'capitalize' }
];

export const textAlignOptions: SettingOption[] = [
    { label: 'Left', value: 'left', icon: AlignLeft },
    { label: 'Center', value: 'center', icon: AlignCenter },
    { label: 'Right', value: 'right', icon: AlignRight },
    { label: 'Justify', value: 'justify', icon: AlignJustify }
];

export const textDecorationOptions: SettingOption[] = [
    { label: 'None', value: 'none' },
    { label: 'Underline', value: 'underline' },
    { label: 'Line-through', value: 'line-through' }
];

/**
 * Complete typography settings for a text element
 */
export const typographySettings: SettingDefinition[] = [
    { type: 'header', label: 'Typography', tab: 'style' },
    {
        key: 'fontFamily',
        type: 'select',
        label: 'Font Family',
        options: fontFamilyOptions,
        default: 'inherit',
        tab: 'style'
    },
    {
        key: 'fontSize',
        type: 'slider',
        label: 'Font Size',
        min: 8,
        max: 120,
        step: 1,
        unit: 'px',
        default: 16,
        responsive: true,
        tab: 'style'
    },
    {
        key: 'fontWeight',
        type: 'toggle_group',
        label: 'Font Weight',
        options: fontWeightOptions,
        default: '400',
        tab: 'style'
    },
    {
        key: 'lineHeight',
        type: 'slider',
        label: 'Line Height',
        min: 0.8,
        max: 3,
        step: 0.05,
        default: 1.5,
        tab: 'style'
    },
    {
        key: 'letterSpacing',
        type: 'slider',
        label: 'Letter Spacing',
        min: -5,
        max: 20,
        step: 0.5,
        unit: 'px',
        default: 0,
        tab: 'style'
    },
    {
        key: 'textAlign',
        type: 'toggle_group',
        label: 'Alignment',
        options: textAlignOptions,
        default: 'left',
        responsive: true,
        tab: 'style'
    },
    {
        key: 'textTransform',
        type: 'select',
        label: 'Transform',
        options: textTransformOptions,
        default: 'none',
        tab: 'style'
    },
    {
        key: 'textDecoration',
        type: 'select',
        label: 'Decoration',
        options: textDecorationOptions,
        default: 'none',
        tab: 'style'
    },
    {
        key: 'textColor',
        type: 'color',
        label: 'Text Color',
        default: 'inherit',
        tab: 'style'
    }
];

/**
 * Heading-specific typography (larger defaults, bolder)
 */
export const headingTypographySettings: SettingDefinition[] = typographySettings.map(setting => {
    if (setting.key === 'fontSize') {
        return { ...setting, default: 32, min: 16, max: 120 };
    }
    if (setting.key === 'fontWeight') {
        return { ...setting, default: '700' };
    }
    if (setting.key === 'lineHeight') {
        return { ...setting, default: 1.2 };
    }
    return setting;
});

/**
 * Get typography default values
 */
export const typographyDefaults: ModuleSettings = {
    fontFamily: 'inherit',
    fontSize: 16,
    fontWeight: '400',
    lineHeight: 1.5,
    letterSpacing: 0,
    textAlign: 'left',
    textTransform: 'none',
    textDecoration: 'none',
    textColor: 'inherit'
};

export const headingTypographyDefaults: ModuleSettings = {
    ...typographyDefaults,
    fontSize: 32,
    fontWeight: '700',
    lineHeight: 1.2
};
