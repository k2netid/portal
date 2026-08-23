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
    adminLabelSettings,
    cssSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings,
    linkSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Icon Module Definition
 */
const IconModule: ModuleDefinition = {
    name: 'icon',
    title: 'Icon',
    icon: 'Star',
    category: 'basic',

    children: null,

    defaults: {
        icon: 'Star',
        size: 48,
        color: '#2059ea',
        alignment: 'center',
        layout_type: 'block',
        gap_x: '0px',
        gap_y: '0px',
        aria_label: '',
        html_id: '',
        use_glow: false,
        glow_color: 'rgba(32, 89, 234, 0.5)',
        use_background: false,
        background_shape: 'circle',
        background_color: '#f3f4f6',
        use_gradient: false,
        hover_color: '',
        hover_background_color: '',
        hover_scale: 1.1,
        padding: { top: 10, bottom: 10, left: 10, right: 10, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        width: 'auto'
    },

    settings: {
        content: [
            {
                id: 'icon_content',
                label: 'Icon Settings',
                fields: [
                    { name: 'icon', type: 'icon', label: 'Select Icon', responsive: true },
                    { name: 'size', type: 'range', label: 'Icon Size', min: 12, max: 200, unit: 'px', responsive: true },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            linkSettings,
            adminLabelSettings('Icon')
        ],
        design: [
            layoutSettings,
            {
                id: 'premium_styling',
                label: 'Premium Styling',
                fields: [
                    { name: 'color', type: 'color', label: 'Icon Color', responsive: true, show_if: { field: 'use_gradient', value: false } },
                    { name: 'use_gradient', type: 'toggle', label: 'Enable Icon Gradient' },
                    { name: 'gradient', type: 'gradient', label: 'Icon Gradient', show_if: { field: 'use_gradient', value: true } },
                    { name: 'use_glow', type: 'toggle', label: 'Enable Neon Glow' },
                    { name: 'glow_color', type: 'color', label: 'Glow Color', show_if: { field: 'use_glow', value: true } },
                    { name: 'use_background', type: 'toggle', label: 'Enable Background Shape' },
                    {
                        name: 'background_shape',
                        type: 'select',
                        label: 'Shape',
                        options: [
                            { label: 'Circle', value: 'circle' },
                            { label: 'Squircle', value: 'squircle' },
                            { label: 'Diamond', value: 'diamond' },
                            { label: 'Organic Blob', value: 'blob1' }
                        ],
                        show_if: { field: 'use_background', value: true }
                    },
                    { name: 'background_color', type: 'color', label: 'Shape Background', show_if: { field: 'use_background', value: true } },
                    {
                        name: 'hover_states',
                        type: 'group',
                        label: 'Hover States',
                        fields: [
                            { name: 'hover_color', type: 'color', label: 'Hover Icon Color' },
                            { name: 'hover_background_color', type: 'color', label: 'Hover Background Color', show_if: { field: 'use_background', value: true } },
                            { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 2, step: 0.1, default: 1.1 }
                        ]
                    }
                ]
            },
            {
                id: 'alignment',
                label: 'Alignment',
                fields: [
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Icon Alignment',
                        options: [
                            { value: 'left', icon: 'AlignLeft' },
                            { value: 'center', icon: 'AlignCenter' },
                            { value: 'right', icon: 'AlignRight' }
                        ],
                        responsive: true
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
}

export default IconModule;
