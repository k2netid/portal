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
 * Counter Module Definition
 */
const CounterModule: ModuleDefinition = {
    name: 'counter',
    title: 'Number Counter',
    icon: 'Hash',
    category: 'content',

    children: null,

    defaults: {
        number: 100,
        prefix: '',
        suffix: '%',
        title: 'Success Rate',
        // Animation
        animateOnView: true,
        duration: 2000,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 20, bottom: 20, left: 20, right: 20, unit: 'px' },
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
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'counter',
                label: 'Counter',
                fields: [
                    { name: 'number', type: 'text', label: 'Number', responsive: true },
                    { name: 'prefix', type: 'text', label: 'Prefix (e.g., $)', responsive: true },
                    { name: 'suffix', type: 'text', label: 'Suffix (e.g., %)', responsive: true },
                    { name: 'decimals', type: 'range', label: 'Decimals', min: 0, max: 4, step: 1, responsive: true },
                    { name: 'separator', type: 'toggle', label: 'Thousands Separator', responsive: true },
                    { name: 'title', type: 'text', label: 'Title', responsive: true },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            {
                id: 'animation',
                label: 'Animation',
                fields: [
                    {
                        name: 'animateOnView',
                        type: 'toggle',
                        label: 'Animate on View'
                    },
                    {
                        name: 'duration',
                        type: 'range',
                        label: 'Duration',
                        min: 500,
                        max: 5000,
                        step: 100,
                        unit: 'ms',
                        responsive: true
                    }
                ]
            },
            linkSettings,
            backgroundSettings,
            orderSettings,
            adminLabelSettings('Counter')
        ],
        design: [
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Alignment',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
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

export default CounterModule;
