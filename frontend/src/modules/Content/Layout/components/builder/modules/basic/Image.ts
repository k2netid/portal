import type { ModuleDefinition } from '@/types/builder';
import {
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
    linkSettings,
    adminLabelSettings,
    cssSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Image Module Definition
 */
const ImageModule: ModuleDefinition = {
    name: 'image',
    title: 'Image',
    icon: 'Image',
    category: 'basic',

    children: null,

    defaults: {
        src: '',
        alt: '',
        title: '',
        showCaption: false,
        caption: '',
        alignment: 'center',
        layout_type: 'block',
        gap_x: '0px',
        gap_y: '0px',
        aria_label: '',
        html_id: '',
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        width: '100%',
        forceFullwidth: false,
        hover_scale: 1.05,
        hover_brightness: 100,
        hover_shadow: 'none'
    },

    settings: {
        content: [
            {
                id: 'content',
                label: 'Content',
                fields: [
                    { name: 'src', type: 'upload', label: 'Image Source', responsive: true },
                    { name: 'alt', type: 'text', label: 'Alt Text' },
                    { name: 'title', type: 'text', label: 'Title Text' },
                    { name: 'showCaption', type: 'toggle', label: 'Show Caption', default: false },
                    { name: 'caption', type: 'text', label: 'Caption', show_if: { field: 'showCaption', value: true } },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            linkSettings,
            adminLabelSettings('Image')
        ],
        design: [
            layoutSettings,
            {
                id: 'alignment',
                label: 'Alignment',
                fields: [
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Image Alignment',
                        options: [
                            { value: 'left', icon: 'AlignLeft' },
                            { value: 'center', icon: 'AlignCenter' },
                            { value: 'right', icon: 'AlignRight' }
                        ],
                        responsive: true
                    },
                    { name: 'forceFullwidth', type: 'toggle', label: 'Force Fullwidth', default: false }
                ]
            },
            {
                id: 'styling_presets',
                label: 'Premium Effects',
                fields: [
                    {
                        name: 'mask_shape',
                        type: 'select',
                        label: 'Image Mask Shape',
                        options: [
                            { label: 'None', value: 'none' },
                            { label: 'Circle', value: 'circle' },
                            { label: 'Squircle', value: 'squircle' },
                            { label: 'Diamond', value: 'diamond' },
                            { label: 'Triangle', value: 'triangle' },
                            { label: 'Organic Blob', value: 'blob1' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'hover_effect',
                        type: 'select',
                        label: 'Hover Style Preset',
                        options: [
                            { label: 'None', value: 'none' },
                            { label: 'Zoom In', value: 'zoom' },
                            { label: 'Tilt 3D', value: 'tilt' },
                            { label: 'Lift & Shadow', value: 'lift' },
                            { label: 'Grayscale to Color', value: 'reveal' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'interactive_states',
                        type: 'group',
                        label: 'Interactive States',
                        fields: [
                            { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1.05 },
                            { name: 'hover_brightness', type: 'range', label: 'Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 },
                            {
                                name: 'hover_shadow', type: 'select', label: 'Hover Shadow', options: [
                                    { label: 'None', value: 'none' },
                                    { label: 'Subtle', value: 'shadow-sm' },
                                    { label: 'Medium', value: 'shadow-md' },
                                    { label: 'Large', value: 'shadow-lg' },
                                    { label: 'Extra Large', value: 'shadow-xl' },
                                    { label: '2XL Neon', value: 'shadow-2xl' }
                                ]
                            }
                        ]
                    }
                ]
            },
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            filterSettings,
            transformSettings,
            animationSettings
        ],
        advanced: [
            visibilitySettings,
            positionSettings,
            transitionSettings,
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
};

export default ImageModule;
