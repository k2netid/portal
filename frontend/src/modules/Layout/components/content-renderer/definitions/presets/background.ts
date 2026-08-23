import type { SettingOption, SettingDefinition, ModuleSettings } from '@/types/builder';

/**
 * Background Property Presets
 * Color, image, gradient, and overlay settings
 */

export const bgSizeOptions: SettingOption[] = [
    { label: 'Cover', value: 'cover' },
    { label: 'Contain', value: 'contain' },
    { label: 'Auto', value: 'auto' },
    { label: '100%', value: '100% 100%' }
];

export const bgPositionOptions: SettingOption[] = [
    { label: 'Center', value: 'center' },
    { label: 'Top', value: 'top' },
    { label: 'Bottom', value: 'bottom' },
    { label: 'Left', value: 'left' },
    { label: 'Right', value: 'right' },
    { label: 'Top Left', value: 'top left' },
    { label: 'Top Right', value: 'top right' },
    { label: 'Bottom Left', value: 'bottom left' },
    { label: 'Bottom Right', value: 'bottom right' }
];

export const bgRepeatOptions: SettingOption[] = [
    { label: 'No Repeat', value: 'no-repeat' },
    { label: 'Repeat', value: 'repeat' },
    { label: 'Repeat X', value: 'repeat-x' },
    { label: 'Repeat Y', value: 'repeat-y' }
];

export const bgAttachmentOptions: SettingOption[] = [
    { label: 'Scroll', value: 'scroll' },
    { label: 'Fixed', value: 'fixed' },
    { label: 'Local', value: 'local' }
];

export const gradientDirectionOptions: SettingOption[] = [
    { label: 'To Right', value: 'to right' },
    { label: 'To Left', value: 'to left' },
    { label: 'To Bottom', value: 'to bottom' },
    { label: 'To Top', value: 'to top' },
    { label: 'Diagonal ↘', value: 'to bottom right' },
    { label: 'Diagonal ↙', value: 'to bottom left' },
    { label: 'Diagonal ↗', value: 'to top right' },
    { label: 'Diagonal ↖', value: 'to top left' },
    { label: 'Radial', value: 'radial' }
];

/**
 * Complete background settings
 */
export const backgroundSettings: SettingDefinition[] = [
    { type: 'header', label: 'Background', tab: 'style' },
    {
        key: 'bgType',
        type: 'toggle_group',
        label: 'Background Type',
        options: [
            { label: 'None', value: 'none' },
            { label: 'Color', value: 'color' },
            { label: 'Image', value: 'image' },
            { label: 'Gradient', value: 'gradient' }
        ],
        default: 'none',
        tab: 'style'
    },
    {
        key: 'bgColor',
        type: 'color',
        label: 'Background Color',
        default: 'transparent',
        condition: (settings) => settings.bgType === 'color',
        tab: 'style'
    },
    {
        key: 'bgImage',
        type: 'image',
        label: 'Background Image',
        default: '',
        condition: (settings) => settings.bgType === 'image',
        tab: 'style'
    },
    {
        key: 'bgSize',
        type: 'select',
        label: 'Background Size',
        options: bgSizeOptions,
        default: 'cover',
        condition: (settings) => settings.bgType === 'image',
        tab: 'style'
    },
    {
        key: 'bgPosition',
        type: 'select',
        label: 'Background Position',
        options: bgPositionOptions,
        default: 'center',
        condition: (settings) => settings.bgType === 'image',
        tab: 'style'
    },
    {
        key: 'bgRepeat',
        type: 'select',
        label: 'Background Repeat',
        options: bgRepeatOptions,
        default: 'no-repeat',
        condition: (settings) => settings.bgType === 'image',
        tab: 'style'
    },
    {
        key: 'bgAttachment',
        type: 'select',
        label: 'Background Attachment',
        options: bgAttachmentOptions,
        default: 'scroll',
        condition: (settings) => settings.bgType === 'image',
        tab: 'style'
    },
    {
        key: 'gradientStart',
        type: 'color',
        label: 'Gradient Start',
        default: '#3b82f6',
        condition: (settings) => settings.bgType === 'gradient',
        tab: 'style'
    },
    {
        key: 'gradientEnd',
        type: 'color',
        label: 'Gradient End',
        default: '#8b5cf6',
        condition: (settings) => settings.bgType === 'gradient',
        tab: 'style'
    },
    {
        key: 'gradientDirection',
        type: 'select',
        label: 'Gradient Direction',
        options: gradientDirectionOptions,
        default: 'to right',
        condition: (settings) => settings.bgType === 'gradient',
        tab: 'style'
    },
    { type: 'header', label: 'Overlay', tab: 'style' },
    {
        key: 'overlayEnabled',
        type: 'boolean',
        label: 'Enable Overlay',
        default: false,
        tab: 'style'
    },
    {
        key: 'overlayColor',
        type: 'color',
        label: 'Overlay Color',
        default: 'rgba(0, 0, 0, 0.5)',
        condition: (settings) => !!settings.overlayEnabled,
        tab: 'style'
    },
    {
        key: 'overlayOpacity',
        type: 'slider',
        label: 'Overlay Opacity',
        min: 0,
        max: 100,
        step: 5,
        default: 50,
        condition: (settings) => !!settings.overlayEnabled,
        tab: 'style'
    }
];

/**
 * Simple background settings (just color + image)
 */
export const simpleBackgroundSettings: SettingDefinition[] = [
    {
        key: 'bgColor',
        type: 'color',
        label: 'Background Color',
        default: 'transparent',
        tab: 'style'
    },
    {
        key: 'bgImage',
        type: 'image',
        label: 'Background Image',
        default: '',
        tab: 'style'
    }
];

/**
 * Background defaults
 */
export const backgroundDefaults: ModuleSettings = {
    bgType: 'none',
    bgColor: 'transparent',
    bgImage: '',
    bgSize: 'cover',
    bgPosition: 'center',
    bgRepeat: 'no-repeat',
    bgAttachment: 'scroll',
    gradientStart: '#3b82f6',
    gradientEnd: '#8b5cf6',
    gradientDirection: 'to right',
    overlayEnabled: false,
    overlayColor: 'rgba(0, 0, 0, 0.5)',
    overlayOpacity: 50
};

/**
 * Generate CSS background style object
 */
export const getBackgroundStyle = (settings: ModuleSettings) => {
    const style: Record<string, string | number | undefined> = {};

    switch (settings.bgType) {
        case 'color':
            style.backgroundColor = settings.bgColor as string;
            break;
        case 'image':
            if (settings.bgImage) {
                style.backgroundImage = `url(${settings.bgImage})`;
                style.backgroundSize = (settings.bgSize as string) || 'cover';
                style.backgroundPosition = (settings.bgPosition as string) || 'center';
                style.backgroundRepeat = (settings.bgRepeat as string) || 'no-repeat';
                style.backgroundAttachment = (settings.bgAttachment as string) || 'scroll';
            }
            break;
        case 'gradient': {
            const direction = (settings.gradientDirection as string) || 'to right';
            if (direction === 'radial') {
                style.backgroundImage = `radial-gradient(circle, ${settings.gradientStart}, ${settings.gradientEnd})`;
            } else {
                style.backgroundImage = `linear-gradient(${direction}, ${settings.gradientStart}, ${settings.gradientEnd})`;
            }
            break;
        }
    }

    return style;
};
