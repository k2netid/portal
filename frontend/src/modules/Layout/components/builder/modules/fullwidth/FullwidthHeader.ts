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
 * Fullwidth Header Module Definition
 */
const FullwidthHeaderModule: ModuleDefinition = {
    name: 'fullwidthheader',
    title: 'Fullwidth Header',
    icon: 'Layout',
    category: 'fullwidth',

    children: null,

    defaults: {
        title: 'Welcome to Our Website',
        subtitle: 'We create amazing digital experiences',
        buttonText: 'Get Started',
        buttonUrl: '#',
        button2Text: 'Learn More',
        button2Url: '#',
        showButton2: true,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },

        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,

        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'text',
                label: 'Text',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'subtitle', type: 'textarea', label: 'Subtitle' }
                ]
            },
            {
                id: 'buttons',
                label: 'Buttons',
                fields: [
                    { name: 'buttonText', type: 'text', label: 'Primary Button Text' },
                    { name: 'buttonUrl', type: 'text', label: 'Primary Button URL' },
                    { name: 'showButton2', type: 'toggle', label: 'Show Secondary Button' },
                    { name: 'button2Text', type: 'text', label: 'Secondary Button Text' },
                    { name: 'button2Url', type: 'text', label: 'Secondary Button URL' }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Fullwidth Header')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'height', type: 'range', label: 'Height', min: 300, max: 1000, step: 50, unit: 'px', responsive: true },
                    { name: 'contentAlignment', type: 'buttonGroup', label: 'Vertical', options: [{ value: 'top', label: 'Top' }, { value: 'center', label: 'Center' }, { value: 'bottom', label: 'Bottom' }] },
                    { name: 'textAlignment', type: 'buttonGroup', label: 'Horizontal', responsive: true, options: [{ value: 'left', label: 'Left', icon: 'AlignLeft' }, { value: 'center', label: 'Center', icon: 'AlignCenter' }, { value: 'right', label: 'Right', icon: 'AlignRight' }] }
                ]
            },
            {
                id: 'titleTypography',
                label: 'Title Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
                }))
            },
            {
                id: 'subtitleTypography',
                label: 'Subtitle Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `subtitle_${f.name}`,
                    label: `Subtitle ${f.label}`
                }))
            },
            {
                id: 'button1Typography',
                label: 'Primary Button Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `button1_${f.name}`,
                    label: `Button 1 ${f.label}`
                }))
            },
            {
                id: 'button2Typography',
                label: 'Secondary Button Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `button2_${f.name}`,
                    label: `Button 2 ${f.label}`
                }))
            },
            {
                id: 'button1Styling',
                label: 'Primary Button Style',
                fields: [
                    { name: 'buttonBackgroundColor', type: 'color', label: 'Background Color' }
                ]
            },
            {
                id: 'button2Styling',
                label: 'Secondary Button Style',
                fields: [
                    { name: 'button2BackgroundColor', type: 'color', label: 'Background Color' }
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

export default FullwidthHeaderModule;
