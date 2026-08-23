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
 * Fullwidth Image Module Definition
 */
const FullwidthImageModule: ModuleDefinition = {
    name: 'fullwidthimage',
    title: 'Fullwidth Image',
    icon: 'Image',
    category: 'fullwidth',

    children: null,

    defaults: {
        image: '',
        alt: '',
        caption: '',
        link: '',
        target: '_self',
        // Sizing
        height: 500,
        objectFit: 'cover',
        // Overlay
        showOverlay: false,
        overlayColor: 'rgba(0,0,0,0.4)',
        overlayText: '',
        overlayTextColor: '#ffffff',
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
                id: 'image',
                label: 'Image',
                fields: [
                    { name: 'image', type: 'upload', label: 'Image' },
                    { name: 'alt', type: 'text', label: 'Alt Text' },
                    { name: 'caption', type: 'text', label: 'Caption' }
                ]
            },
            {
                id: 'link',
                label: 'Link',
                fields: [
                    { name: 'link', type: 'text', label: 'URL' },
                    { name: 'target', type: 'select', label: 'Target', options: [{ value: '_self', label: 'Same Window' }, { value: '_blank', label: 'New Window' }] }
                ]
            },
            {
                id: 'overlay',
                label: 'Overlay',
                fields: [
                    { name: 'showOverlay', type: 'toggle', label: 'Show Overlay' },
                    { name: 'overlayColor', type: 'color', label: 'Overlay Color' },
                    { name: 'overlayText', type: 'text', label: 'Overlay Text' },
                    { name: 'overlayTextColor', type: 'color', label: 'Text Color' }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Fullwidth Image')
        ],
        design: [
            {
                id: 'imageStyle',
                label: 'Image Style',
                fields: [
                    { name: 'height', type: 'range', label: 'Height', min: 200, max: 800, step: 50, unit: 'px', responsive: true },
                    { name: 'objectFit', type: 'select', label: 'Object Fit', options: [{ value: 'cover', label: 'Cover' }, { value: 'contain', label: 'Contain' }, { value: 'fill', label: 'Fill' }] }
                ]
            },
            {
                id: 'overlayTypography',
                label: 'Overlay Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `overlay_${f.name}`,
                    label: `Overlay ${f.label}`
                }))
            },
            {
                id: 'captionTypography',
                label: 'Caption Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `caption_${f.name}`,
                    label: `Caption ${f.label}`
                }))
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

export default FullwidthImageModule;
