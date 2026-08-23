import type { ModuleDefinition, SettingDefinition } from '@/types/builder';
import {
    spacingSettings,
    boxShadowSettings,
    filterSettings,
    transformSettings,
    animationSettings,
    visibilitySettings,
    positionSettings,
    transitionSettings,
    linkSettings,
    adminLabelSettings,
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Button Module Definition
 */
const ButtonModule: ModuleDefinition = {
    name: 'button',
    title: 'Button',
    icon: 'MousePointer',
    category: 'basic',

    children: null,

    defaults: {
        text: 'Click Here',
        link_url: '#',
        link_target: '_self',
        alignment: 'left',
        variant: 'solid', // solid, outline, ghost, glass, gradient
        color: '#ffffff',
        use_icon: false,
        iconName: 'ArrowRight',
        iconPosition: 'right',
        iconSize: 16,
        hover_effect: 'lift', // none, lift, zoom, pulse, shine, sweep
        background: { color: '#111827', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 12, bottom: 12, left: 30, right: 30, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        width: 'auto',
        use_custom_styles: false,
        gradient: {
            type: 'linear',
            direction: '135deg',
            stops: [
                { color: '#6366f1', position: 0 },
                { color: '#a855f7', position: 100 }
            ]
        }
    },

    settings: {
        content: [
            {
                id: 'content',
                label: 'Content',
                fields: [
                    { name: 'text', type: 'text', label: 'Button Text', responsive: true },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label', placeholder: 'Screen reader description', responsive: true },
                    linkSettings
                ]
            },
            {
                id: 'link_attributes',
                label: 'Link Attributes',
                fields: [
                    {
                        name: 'link_rel',
                        type: 'select',
                        label: 'Relationship (Rel)',
                        multiple: true,
                        options: [
                            { label: 'NoFollow', value: 'nofollow' },
                            { label: 'NoReferrer', value: 'noreferrer' },
                            { label: 'NoOpener', value: 'noopener' },
                            { label: 'Sponsored', value: 'sponsored' }
                        ]
                    },
                    { name: 'download', type: 'toggle', label: 'Download Link' },
                    {
                        name: 'button_type',
                        type: 'select',
                        label: 'Button HTML Type',
                        options: [
                            { label: 'Link/Action', value: 'button' },
                            { label: 'Submit Form', value: 'submit' },
                            { label: 'Reset Form', value: 'reset' }
                        ],
                        default: 'button'
                    }
                ]
            },
            adminLabelSettings('Button')
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
                        label: 'Button Alignment',
                        options: [
                            { value: 'left', icon: 'AlignLeft' },
                            { value: 'center', icon: 'AlignCenter' },
                            { value: 'right', icon: 'AlignRight' }
                        ],
                        responsive: true
                    }
                ]
            },
            {
                id: 'text',
                label: 'Text',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: f.name === 'text_align' ? 'button_text_align' : f.name
                }))
            },
            {
                id: 'button_custom',
                label: 'Button',
                fields: [
                    {
                        name: 'variant',
                        type: 'select',
                        label: 'Button Style',
                        options: [
                            { label: 'Solid', value: 'solid' },
                            { label: 'Outline', value: 'outline' },
                            { label: 'Ghost', value: 'ghost' },
                            { label: 'Glassmorphism', value: 'glass' },
                            { label: 'Gradient', value: 'gradient' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'hover_effect',
                        type: 'select',
                        label: 'Hover Animation',
                        options: [
                            { label: 'None', value: 'none' },
                            { label: 'Lift Up', value: 'lift' },
                            { label: 'Zoom In', value: 'zoom' },
                            { label: 'Pulse Glow', value: 'pulse' },
                            { label: 'Shine Flash', value: 'shine' },
                            { label: 'Slide Sweep', value: 'sweep' }
                        ],
                        responsive: true
                    },
                    { name: 'width', type: 'dimension', label: 'Width', responsive: true },
                    { name: 'glass_blur', type: 'range', label: 'Glass Blur', min: 0, max: 20, unit: 'px', show_if: { field: 'variant', value: 'glass' } },
                    { name: 'glass_opacity', type: 'range', label: 'Glass Opacity', min: 0, max: 100, unit: '%', show_if: { field: 'variant', value: 'glass' } },
                    { name: 'gradient', type: 'gradient', label: 'Button Gradient', show_if: { field: 'variant', value: 'gradient' } },
                    {
                        name: 'use_custom_styles',
                        type: 'toggle',
                        label: 'Use Custom Styles For Button',
                        default: false
                    },
                    {
                        name: 'group_button_background',
                        type: 'group',
                        label: 'Button Background',
                        show_if: { field: 'use_custom_styles', value: true },
                        fields: [
                            {
                                name: 'background',
                                type: 'background',
                                label: 'Background',
                                responsive: true
                            }
                        ]
                    },
                    {
                        name: 'group_button_border',
                        type: 'group',
                        label: 'Button Border',
                        show_if: { field: 'use_custom_styles', value: true },
                        fields: [
                            {
                                name: 'border',
                                type: 'border',
                                label: 'Border',
                                responsive: true
                            }
                        ]
                    },
                    {
                        name: 'group_button_text',
                        type: 'group',
                        label: 'Button Text',
                        show_if: { field: 'use_custom_styles', value: true },
                        fields: [
                            {
                                name: 'text_color',
                                type: 'color',
                                label: 'Text Color',
                                responsive: true
                            },
                            {
                                name: 'font_size',
                                type: 'range',
                                label: 'Font Size',
                                min: 8,
                                max: 100,
                                unit: 'px',
                                responsive: true
                            },
                            {
                                name: 'hover_background_color',
                                type: 'color',
                                label: 'Hover Background Color',
                                responsive: true
                            },
                            {
                                name: 'hover_text_color',
                                type: 'color',
                                label: 'Hover Text Color',
                                responsive: true
                            }
                        ]
                    },
                    {
                        name: 'group_button_icon',
                        type: 'group',
                        label: 'Button Icon',
                        show_if: { field: 'use_custom_styles', value: true },
                        fields: [
                            {
                                name: 'use_icon',
                                type: 'toggle',
                                label: 'Show Icon'
                            },
                            {
                                name: 'iconName',
                                type: 'icon',
                                label: 'Icon',
                                show_if: { field: 'use_icon', value: true }
                            },
                            {
                                name: 'iconPosition',
                                type: 'buttonGroup',
                                label: 'Icon Position',
                                options: [
                                    { label: 'Left', value: 'left', icon: 'AlignLeft' },
                                    { label: 'Right', value: 'right', icon: 'AlignRight' }
                                ],
                                show_if: { field: 'use_icon', value: true }
                            },
                            {
                                name: 'iconSize',
                                type: 'range',
                                label: 'Icon Size',
                                min: 8,
                                max: 120,
                                unit: 'px',
                                show_if: { field: 'use_icon', value: true }
                            }
                        ]
                    }
                ]
            },
            spacingSettings,
            boxShadowSettings,
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

export default ButtonModule;
