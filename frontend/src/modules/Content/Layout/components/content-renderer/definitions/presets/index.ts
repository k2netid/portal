/**
 * Property Presets Index
 * Export all presets for easy import
 */

import type { SettingDefinition, ModuleSettings } from '@/types/builder';

// Typography
export {
    typographySettings,
    headingTypographySettings,
    typographyDefaults,
    headingTypographyDefaults,
    fontFamilyOptions,
    fontWeightOptions,
    textAlignOptions,
    textTransformOptions,
    textDecorationOptions
} from './typography';

// Spacing
export {
    spacingSettings,
    paddingSettings,
    marginSettings,
    simplePaddingSettings,
    gapSettings,
    spacingDefaults,
    spacingOptions
} from './spacing';

// Background
export {
    backgroundSettings,
    simpleBackgroundSettings,
    backgroundDefaults,
    getBackgroundStyle,
    bgSizeOptions,
    bgPositionOptions,
    bgRepeatOptions,
    gradientDirectionOptions
} from './background';

// Border
export {
    borderSettings,
    simpleRadiusSettings,
    borderDefaults,
    getBorderStyle,
    borderStyleOptions,
    borderRadiusOptions,
    borderRadiusPresets
} from './border';

// Effects
export {
    effectsSettings,
    shadowSettings,
    opacitySettings,
    filterSettings,
    transformSettings,
    effectsDefaults,
    getFilterStyle,
    getTransformStyle,
    shadowOptions,
    blendModeOptions
} from './effects';

// Position
export {
    layoutSettings,
    positionSettings,
    sizingSettings,
    flexSettings,
    overflowSettings,
    positionDefaults,
    positionModeOptions,
    displayOptions,
    flexDirectionOptions,
    justifyContentOptions,
    alignItemsOptions,
    overflowOptions
} from './position';

/**
 * Standard component property sets
 * Use these to quickly add common properties to block definitions
 */
import { typographySettings, typographyDefaults } from './typography';
import { borderSettings, borderDefaults } from './border';
import { spacingDefaults } from './spacing';
import { backgroundDefaults } from './background';
import { effectsDefaults } from './effects';
import { positionDefaults } from './position';

// Text component properties
export const textComponentSettings: SettingDefinition[] = [
    ...typographySettings
];

export const textComponentDefaults: ModuleSettings = {
    ...typographyDefaults
};

// Image component properties
export const imageComponentSettings: SettingDefinition[] = [
    { type: 'header', label: 'Image', tab: 'content' },
    { key: 'src', type: 'image', label: 'Image Source', default: '', tab: 'content' },
    { key: 'alt', type: 'text', label: 'Alt Text', default: '', tab: 'content' },
    { type: 'header', label: 'Size', tab: 'style' },
    {
        key: 'objectFit', type: 'select', label: 'Object Fit', options: [
            { label: 'Cover', value: 'cover' },
            { label: 'Contain', value: 'contain' },
            { label: 'Fill', value: 'fill' },
            { label: 'None', value: 'none' }
        ], default: 'cover', tab: 'style'
    },
    {
        key: 'aspectRatio', type: 'select', label: 'Aspect Ratio', options: [
            { label: 'Auto', value: 'auto' },
            { label: '1:1', value: '1/1' },
            { label: '4:3', value: '4/3' },
            { label: '16:9', value: '16/9' },
            { label: '21:9', value: '21/9' }
        ], default: 'auto', tab: 'style'
    },
    ...borderSettings
];

export const imageComponentDefaults: ModuleSettings = {
    src: '',
    alt: '',
    objectFit: 'cover',
    aspectRatio: 'auto',
    ...borderDefaults
};

// Button component properties
export const buttonComponentSettings: SettingDefinition[] = [
    { type: 'header', label: 'Button', tab: 'content' },
    { key: 'text', type: 'text', label: 'Button Text', default: 'Click Here', tab: 'content' },
    { key: 'url', type: 'text', label: 'Link URL', default: '#', tab: 'content' },
    { key: 'openNewTab', type: 'boolean', label: 'Open in New Tab', default: false, tab: 'content' },
    { type: 'header', label: 'Style', tab: 'style' },
    {
        key: 'variant', type: 'toggle_group', label: 'Variant', options: [
            { label: 'Solid', value: 'primary' },
            { label: 'Soft', value: 'secondary' },
            { label: 'Outline', value: 'outline' },
            { label: 'Ghost', value: 'ghost' }
        ], default: 'primary', tab: 'style'
    },
    {
        key: 'size', type: 'toggle_group', label: 'Size', options: [
            { label: 'Sm', value: 'small' },
            { label: 'Md', value: 'medium' },
            { label: 'Lg', value: 'large' }
        ], default: 'medium', tab: 'style'
    },
    { key: 'fullWidth', type: 'boolean', label: 'Full Width', default: false, tab: 'style' },
    ...typographySettings.filter(s => ['fontWeight', 'textTransform'].includes(s.key || '')),
    ...borderSettings.filter(s => s.key === 'borderRadius' || s.type === 'header')
];

export const buttonComponentDefaults: ModuleSettings = {
    text: 'Click Here',
    url: '#',
    openNewTab: false,
    variant: 'primary',
    size: 'medium',
    fullWidth: false,
    fontWeight: '600',
    textTransform: 'none',
    borderRadius: 8
};

// Icon component properties
export const iconComponentSettings: SettingDefinition[] = [
    { type: 'header', label: 'Icon', tab: 'content' },
    { key: 'iconName', type: 'icon_select', label: 'Icon', default: 'star', tab: 'content' },
    { key: 'iconSize', type: 'slider', label: 'Size', min: 12, max: 120, step: 4, unit: 'px', default: 24, tab: 'style' },
    { key: 'iconColor', type: 'color', label: 'Color', default: 'currentColor', tab: 'style' },
    { key: 'iconStrokeWidth', type: 'slider', label: 'Stroke Width', min: 1, max: 4, step: 0.5, default: 2, tab: 'style' }
];

export const iconComponentDefaults: ModuleSettings = {
    iconName: 'star',
    iconSize: 24,
    iconColor: 'currentColor',
    iconStrokeWidth: 2
};

/**
 * Master defaults getter
 */
export const getAllDefaults = (): ModuleSettings => ({
    ...typographyDefaults,
    ...spacingDefaults,
    ...backgroundDefaults,
    ...borderDefaults,
    ...effectsDefaults,
    ...positionDefaults
});
