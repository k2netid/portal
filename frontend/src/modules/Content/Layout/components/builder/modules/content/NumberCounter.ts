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
 * Number Counter Module Definition
 */
const NumberCounterModule: ModuleDefinition = {
    name: 'numbercounter',
    title: 'Number Counter',
    icon: 'Hash',
    category: 'content',

    children: null,

    defaults: {
        // Content
        number: 100,
        prefix: '',
        suffix: '%',
        title: 'Completion',
        description: '',
        // Animation
        duration: 2000,
        animateOnScroll: true,
        easing: 'easeOut',
        separator: true,
        decimals: 0,
        // Layout
        layout: 'vertical',
        alignment: 'center',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 24, bottom: 24, left: 0, right: 0, unit: 'px' },
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
                id: 'number',
                label: 'Number',
                fields: [
                    { name: 'number', type: 'text', label: 'Number', responsive: true },
                    { name: 'prefix', type: 'text', label: 'Prefix', responsive: true },
                    { name: 'suffix', type: 'text', label: 'Suffix', responsive: true },
                    { name: 'decimals', type: 'range', label: 'Decimals', min: 0, max: 4, step: 1, responsive: true },
                    { name: 'separator', type: 'toggle', label: 'Thousands Separator', responsive: true }
                ]
            },
            {
                id: 'text',
                label: 'Text',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'description', type: 'textarea', label: 'Description' },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            {
                id: 'countAnimation',
                label: 'Counter Animation',
                fields: [
                    { name: 'duration', type: 'range', label: 'Duration', min: 500, max: 5000, step: 100, unit: 'ms', responsive: true },
                    { name: 'animateOnScroll', type: 'toggle', label: 'Animate on Scroll', responsive: true },
                    {
                        name: 'easing', type: 'select', label: 'Easing', responsive: true, options: [
                            { value: 'linear', label: 'Linear' },
                            { value: 'easeIn', label: 'Ease In' },
                            { value: 'easeOut', label: 'Ease Out' },
                            { value: 'easeInOut', label: 'Ease In Out' }
                        ]
                    }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Number Counter')
        ],
        design: [
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    {
                        name: 'layout', type: 'select', label: 'Layout', options: [
                            { value: 'vertical', label: 'Vertical' },
                            { value: 'horizontal', label: 'Horizontal' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'alignment', type: 'buttonGroup', label: 'Alignment', options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ],
                        responsive: true
                    }
                ]
            },
            {
                id: 'numberTypography',
                label: 'Number Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `number_${f.name}`,
                    label: `Number ${f.label}`
                }))
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
                id: 'descriptionTypography',
                label: 'Description Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `description_${f.name}`,
                    label: `Description ${f.label}`
                }))
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

export default NumberCounterModule;
