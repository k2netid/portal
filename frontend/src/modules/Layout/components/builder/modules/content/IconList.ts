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
    adminLabelSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Icon List Module Definition
 */
const IconListModule: ModuleDefinition = {
    name: 'iconlist',
    title: 'Icon List',
    icon: 'List',
    category: 'content',

    children: null,

    defaults: {
        items: [
            { text: 'First benefit or feature', icon: 'Check' },
            { text: 'Second benefit or feature', icon: 'Check' },
            { text: 'Third benefit or feature', icon: 'Check' }
        ],
        // Icon
        iconColor: '#22c55e',
        iconSize: 20,
        iconBgColor: '',
        iconBackgroundShape: 'none',
        // Layout
        gap: 12,
        alignment: 'left',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
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
                id: 'items',
                label: 'List Items',
                fields: [
                    {
                        name: 'items',
                        type: 'repeater',
                        label: 'List Items',
                        itemLabel: 'text',
                        fields: [
                            { name: 'text', type: 'text', label: 'Text' },
                            { name: 'icon', type: 'icon', label: 'Icon' }
                        ]
                    }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Icon List')
        ],
        design: [
            {
                id: 'icon',
                label: 'Icon',
                fields: [
                    {
                        name: 'iconColor',
                        type: 'color',
                        label: 'Icon Color'
                    },
                    {
                        name: 'iconSize',
                        type: 'range',
                        label: 'Icon Size',
                        min: 14,
                        max: 32,
                        step: 2,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'iconBgColor',
                        type: 'color',
                        label: 'Icon Background'
                    },
                    {
                        name: 'iconBackgroundShape',
                        type: 'select',
                        label: 'Background Shape',
                        options: [
                            { value: 'none', label: 'None' },
                            { value: 'circle', label: 'Circle' },
                            { value: 'square', label: 'Square' }
                        ]
                    }
                ]
            },
            {
                id: 'textTypography',
                label: 'Text Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `text_${f.name}`,
                    label: `Text ${f.label}`
                }))
            },
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    {
                        name: 'layout',
                        type: 'select',
                        label: 'List Layout',
                        responsive: true,
                        options: [
                            { value: 'vertical', label: 'Vertical (Stacked)' },
                            { value: 'horizontal', label: 'Horizontal (Inline)' }
                        ]
                    },
                    {
                        name: 'gap',
                        type: 'range',
                        label: 'Gap',
                        min: 4,
                        max: 60,
                        step: 2,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Alignment',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' }
                        ]
                    }
                ]
            },
            {
                id: 'premium_interactive',
                label: 'Interactive States',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1 },
                    { name: 'hover_brightness', type: 'range', label: 'Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 }
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
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
};

export default IconListModule;
