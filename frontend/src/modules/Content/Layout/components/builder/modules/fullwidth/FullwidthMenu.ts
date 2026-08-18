import type { ModuleDefinition, SettingDefinition } from '@/types/builder';
import {
    backgroundSettings,
    spacingSettings,
    borderSettings,
    boxShadowSettings,
    sizingSettings,
    filterSettings,
    transformSettings,
    animationSettings,
    visibilitySettings,
    positionSettings,
    transitionSettings,
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings,
    adminLabelSettings,
} from '@/components/builder/modules/commonSettings';

/**
 * Fullwidth Menu Module Definition
 */
const FullwidthMenuModule: ModuleDefinition = {
    name: 'fullwidthmenu',
    title: 'Fullwidth Menu',
    icon: 'Menu',
    category: 'fullwidth',

    children: null,

    defaults: {
        menuId: '',
        style: 'horizontal',
        alignment: 'center',
        // Logo
        showLogo: true,
        logoImage: '',
        logoPosition: 'left',
        logoMaxHeight: 60,
        logoText: '',
        // Mobile
        mobileBreakpoint: 980,
        mobileToggleStyle: 'hamburger',
        // Background
        background: { color: '#ffffff', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Dropdown
        dropdownAnimation: 'fade',
        dropdownWidth: 220,
        // Spacing
        itemSpacing: 24,
        padding: { top: 16, bottom: 16, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },

        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,

        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        // Sticky
        stickyMenu: false,
        stickyBackgroundColor: '#ffffff',
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'menu',
                label: 'Menu',
                fields: [
                    { name: 'menuId', type: 'text', label: 'Menu ID/Slug' },
                    {
                        name: 'style', type: 'select', label: 'Style', options: [
                            { value: 'horizontal', label: 'Horizontal' },
                            { value: 'centered', label: 'Centered' },
                            { value: 'fullwidth', label: 'Fullwidth' }
                        ]
                    }
                ]
            },
            {
                id: 'logo',
                label: 'Logo',
                fields: [
                    { name: 'showLogo', type: 'toggle', label: 'Show Logo' },
                    { name: 'logoImage', type: 'upload', label: 'Logo Image' },
                    {
                        name: 'logoPosition', type: 'select', label: 'Position', options: [
                            { value: 'left', label: 'Left' },
                            { value: 'center', label: 'Center' },
                            { value: 'right', label: 'Right' }
                        ]
                    },
                    { name: 'logoMaxHeight', type: 'range', label: 'Logo Max Height', min: 30, max: 120, step: 5, unit: 'px' }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Fullwidth Menu')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'alignment', type: 'buttonGroup', label: 'Menu Alignment', options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    },
                    { name: 'itemSpacing', type: 'range', label: 'Item Spacing', min: 12, max: 48, step: 4, unit: 'px' }
                ]
            },
            {
                id: 'menuTypography',
                label: 'Menu Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `menu_${f.name}`,
                    label: `Menu ${f.label}`
                }))
            },
            {
                id: 'dropdownColors',
                label: 'Dropdown Colors',
                fields: [
                    { name: 'dropdownBackgroundColor', type: 'color', label: 'Dropdown Background' }
                ]
            },
            {
                id: 'dropdown',
                label: 'Dropdown',
                fields: [
                    {
                        name: 'dropdownAnimation', type: 'select', label: 'Animation', options: [
                            { value: 'none', label: 'None' },
                            { value: 'fade', label: 'Fade' },
                            { value: 'slide', label: 'Slide' },
                            { value: 'zoom', label: 'Zoom' }
                        ]
                    },
                    { name: 'dropdownWidth', type: 'range', label: 'Width', min: 150, max: 350, step: 10, unit: 'px' }
                ]
            },
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            {
                id: 'premium_interactive',
                label: 'Interactive States',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1 },
                    { name: 'hover_brightness', type: 'range', label: 'Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 }
                ]
            },
            filterSettings,
            transformSettings,
            animationSettings
        ],
        advanced: [
            {
                id: 'sticky',
                label: 'Sticky',
                fields: [
                    { name: 'stickyMenu', type: 'toggle', label: 'Sticky Menu' },
                    { name: 'stickyBackgroundColor', type: 'color', label: 'Sticky Background' }
                ]
            },
            {
                id: 'mobile',
                label: 'Mobile',
                fields: [
                    { name: 'mobileBreakpoint', type: 'range', label: 'Breakpoint', min: 768, max: 1200, step: 20, unit: 'px' }
                ]
            },
            visibilitySettings,
            positionSettings,
            transitionSettings,
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
};

export default FullwidthMenuModule;
