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
 * Progress Bar Module Definition
 */
const ProgressBarModule: ModuleDefinition = {
    name: 'progressbar',
    title: 'Progress Bar',
    icon: 'Loader',
    category: 'content',

    children: null,

    defaults: {
        title: 'Progress',
        percentage: 75,
        showPercentage: true,
        // Styling
        barColor: '#2059ea',
        trackColor: '#e0e0e0',
        textColor: '#333333',
        height: 20,
        borderRadius: 10,
        // Layout
        titlePosition: 'above',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 16, left: 0, right: 0, unit: 'px' },
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
                id: 'progress',
                label: 'Progress',
                fields: [
                    {
                        name: 'title',
                        type: 'text',
                        label: 'Title',
                        responsive: true
                    },
                    {
                        name: 'percentage',
                        type: 'range',
                        label: 'Percentage',
                        min: 0,
                        max: 100,
                        step: 1,
                        unit: '%',
                        responsive: true
                    },
                    {
                        name: 'showPercentage',
                        type: 'toggle',
                        label: 'Show Percentage',
                        responsive: true
                    },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Progress Bar')
        ],
        design: [
            {
                id: 'style',
                label: 'Bar Style',
                fields: [
                    {
                        name: 'barColor',
                        type: 'color',
                        label: 'Bar Color',
                        responsive: true
                    },
                    {
                        name: 'trackColor',
                        type: 'color',
                        label: 'Track Color',
                        responsive: true
                    },
                    {
                        name: 'height',
                        type: 'range',
                        label: 'Bar Height',
                        min: 8,
                        max: 40,
                        step: 2,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'borderRadius',
                        type: 'range',
                        label: 'Bar Roundness',
                        min: 0,
                        max: 20,
                        step: 2,
                        unit: 'px',
                        responsive: true
                    }
                ]
            },
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    {
                        name: 'titlePosition',
                        type: 'select',
                        label: 'Title Position',
                        options: [
                            { value: 'above', label: 'Above Bar' },
                            { value: 'inside', label: 'Inside Bar' },
                            { value: 'hidden', label: 'Hidden' }
                        ],
                        responsive: true
                    },
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
                id: 'titleTypography',
                label: 'Title Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
                }))
            },
            {
                id: 'percentageTypography',
                label: 'Percentage Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `percentage_${f.name}`,
                    label: `Percentage ${f.label}`
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

export default ProgressBarModule;
