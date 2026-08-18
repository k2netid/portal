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
    linkSettings,
    orderSettings,
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
 * Toggle Module Definition (Single collapsible)
 */
const ToggleModule: ModuleDefinition = {
    name: 'toggle',
    title: 'Toggle',
    icon: 'ChevronDown',
    category: 'interactive',

    children: null,

    defaults: {
        title: 'Toggle Title',
        content: 'Toggle content goes here. This content is hidden by default and shows when the toggle is clicked.',
        defaultOpen: false,
        // Toggle Icon
        toggleIcon: 'chevron',
        iconPosition: 'right',
        iconColor: '#333333',
        // Style
        headerStyle: 'bordered',
        headerBackgroundColor: '#f5f5f5',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 1, color: '#e0e0e0', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        aria_label: '',
        html_id: '',
        hover_scale: 1.05,
        hover_brightness: 100,
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'text',
                label: 'Text',
                fields: [
                    {
                        name: 'title',
                        type: 'text',
                        label: 'Title',
                        responsive: true
                    },
                    {
                        name: 'content',
                        type: 'richtext',
                        label: 'Content',
                        responsive: true
                    },
                    {
                        name: 'defaultOpen',
                        type: 'toggle',
                        label: 'Open by Default',
                        responsive: true
                    }
                ]
            },
            linkSettings,
            backgroundSettings,
            layoutSettings,
            orderSettings,
            adminLabelSettings('Toggle')
        ],
        design: [
            {
                id: 'toggleIcon',
                label: 'Toggle Icon',
                fields: [
                    {
                        name: 'toggleIcon',
                        type: 'select',
                        label: 'Icon Style',
                        options: [
                            { value: 'chevron', label: 'Chevron' },
                            { value: 'plus', label: 'Plus / Minus' },
                            { value: 'none', label: 'None' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'iconPosition',
                        type: 'buttonGroup',
                        label: 'Icon Position',
                        options: [
                            { value: 'left', label: 'Left' },
                            { value: 'right', label: 'Right' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'iconColor',
                        type: 'color',
                        label: 'Icon Color',
                        responsive: true
                    }
                ]
            },
            {
                id: 'headerStyle',
                label: 'Header Style',
                fields: [
                    {
                        name: 'headerStyle',
                        type: 'select',
                        label: 'Header Preset',
                        options: [
                            { value: 'bordered', label: 'Bordered' },
                            { value: 'filled', label: 'Filled' },
                            { value: 'minimal', label: 'Minimal' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'headerBackgroundColor',
                        type: 'color',
                        label: 'Header Background',
                        responsive: true
                    },
                    {
                        name: 'headerPadding',
                        type: 'spacing',
                        label: 'Header Padding',
                        responsive: true
                    }
                ]
            },
            {
                id: 'headerTypography',
                label: 'Header Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `header_${f.name}`,
                    label: `Header ${f.label}`
                }))
            },
            {
                id: 'contentStyle',
                label: 'Content Style',
                fields: [
                    {
                        name: 'contentBackgroundColor',
                        type: 'color',
                        label: 'Content Background',
                        responsive: true
                    },
                    {
                        name: 'contentPadding',
                        type: 'spacing',
                        label: 'Content Padding',
                        responsive: true
                    }
                ]
            },
            {
                id: 'contentTypography',
                label: 'Content Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `content_${f.name}`,
                    label: `Content ${f.label}`
                }))
            },
            {
                id: 'premium_interactive',
                label: 'Interactive States',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1.05 },
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
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
};

export default ToggleModule;
