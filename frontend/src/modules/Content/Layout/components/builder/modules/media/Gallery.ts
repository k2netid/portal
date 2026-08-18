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
 * Gallery Module Definition
 */
const GalleryModule: ModuleDefinition = {
    name: 'gallery',
    title: 'Gallery',
    icon: 'LayoutGrid',
    category: 'media',

    children: null,

    defaults: {
        columns: 3,
        gap: 16,
        // Layout
        layout: 'grid',
        aspectRatio: '1:1',
        // Lightbox
        lightbox: true,
        // Hover Effect
        hoverEffect: 'zoom',
        overlayColor: 'rgba(0,0,0,0.5)',
        // Captions
        showCaptions: false,
        captionPosition: 'below',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' },
                top: { width: 0, color: '#333333', style: 'solid' },
                right: { width: 0, color: '#333333', style: 'solid' },
                bottom: { width: 0, color: '#333333', style: 'solid' },
                left: { width: 0, color: '#333333', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },

        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,

        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'images',
                label: 'Images',
                fields: [
                    {
                        name: 'images',
                        type: 'repeater',
                        label: 'Gallery Images',
                        addButtonLabel: 'Add Image',
                        fields: [
                            { name: 'url', type: 'image', label: 'Image', responsive: true },
                            { name: 'alt', type: 'text', label: 'Alt Text', responsive: true },
                            { name: 'caption', type: 'text', label: 'Caption', responsive: true }
                        ]
                    }
                ]
            },
            {
                id: 'lightbox',
                label: 'Lightbox',
                fields: [
                    {
                        name: 'lightbox',
                        type: 'toggle',
                        label: 'Enable Lightbox',
                        responsive: true
                    }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Gallery')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'layout',
                        type: 'select',
                        label: 'Layout Style',
                        responsive: true,
                        options: [
                            { value: 'grid', label: 'Grid' },
                            { value: 'masonry', label: 'Masonry' }
                        ]
                    },
                    {
                        name: 'columns',
                        type: 'range',
                        label: 'Columns',
                        min: 1,
                        max: 6,
                        step: 1,
                        responsive: true
                    },
                    {
                        name: 'gap',
                        type: 'range',
                        label: 'Gap',
                        min: 0,
                        max: 48,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'aspectRatio',
                        type: 'select',
                        label: 'Aspect Ratio',
                        responsive: true,
                        options: [
                            { value: '1:1', label: 'Square (1:1)' },
                            { value: '4:3', label: 'Standard (4:3)' },
                            { value: '16:9', label: 'Widescreen (16:9)' },
                            { value: 'auto', label: 'Auto' }
                        ]
                    }
                ]
            },
            {
                id: 'hover',
                label: 'Hover Effect',
                fields: [
                    {
                        name: 'hoverEffect',
                        type: 'select',
                        label: 'Hover Effect',
                        responsive: true,
                        options: [
                            { value: 'none', label: 'None' },
                            { value: 'zoom', label: 'Zoom' },
                            { value: 'overlay', label: 'Overlay' },
                            { value: 'grayscale', label: 'Grayscale' }
                        ]
                    },
                    {
                        name: 'overlayColor',
                        type: 'color',
                        label: 'Overlay Color',
                        responsive: true
                    }
                ]
            },
            {
                id: 'captions',
                label: 'Captions',
                fields: [
                    {
                        name: 'showCaptions',
                        type: 'toggle',
                        label: 'Show Captions',
                        responsive: true
                    },
                    {
                        name: 'captionPosition',
                        type: 'select',
                        label: 'Caption Position',
                        responsive: true,
                        options: [
                            { value: 'below', label: 'Below Image' },
                            { value: 'overlay', label: 'On Overlay' }
                        ],
                        show_if: { field: 'showCaptions', value: true }
                    }
                ]
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

export default GalleryModule;
