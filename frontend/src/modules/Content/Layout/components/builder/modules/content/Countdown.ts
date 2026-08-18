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
 * Countdown Timer Module Definition
 */
const CountdownModule: ModuleDefinition = {
    name: 'countdown',
    title: 'Countdown Timer',
    icon: 'Clock',
    category: 'content',

    children: null,

    defaults: {
        endDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        endTime: '00:00',
        // Display
        showDays: true,
        showHours: true,
        showMinutes: true,
        showSeconds: true,
        // Labels
        daysLabel: 'Days',
        hoursLabel: 'Hours',
        minutesLabel: 'Minutes',
        secondsLabel: 'Seconds',
        // Layout
        alignment: 'center',
        gap: 24,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        itemBackgroundColor: '#f5f5f5',
        itemBorderRadius: 8,
        itemPadding: 20,
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
                id: 'target',
                label: 'Target Date',
                fields: [
                    {
                        name: 'endDate',
                        type: 'text',
                        label: 'Date (YYYY-MM-DD)',
                        responsive: true
                    },
                    {
                        name: 'endTime',
                        type: 'text',
                        label: 'Time (HH:MM)',
                        responsive: true
                    }
                ]
            },
            {
                id: 'display',
                label: 'Display',
                fields: [
                    {
                        name: 'showDays',
                        type: 'toggle',
                        label: 'Show Days'
                    },
                    {
                        name: 'showHours',
                        type: 'toggle',
                        label: 'Show Hours'
                    },
                    {
                        name: 'showMinutes',
                        type: 'toggle',
                        label: 'Show Minutes'
                    },
                    {
                        name: 'showSeconds',
                        type: 'toggle',
                        label: 'Show Seconds'
                    }
                ]
            },
            {
                id: 'labels',
                label: 'Labels',
                fields: [
                    {
                        name: 'daysLabel',
                        type: 'text',
                        label: 'Days Label',
                        responsive: true
                    },
                    {
                        name: 'hoursLabel',
                        type: 'text',
                        label: 'Hours Label',
                        responsive: true
                    },
                    {
                        name: 'minutesLabel',
                        type: 'text',
                        label: 'Minutes Label',
                        responsive: true
                    },
                    {
                        name: 'secondsLabel',
                        type: 'text',
                        label: 'Seconds Label',
                        responsive: true
                    },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Countdown Timer')
        ],
        design: [
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
                id: 'labelTypography',
                label: 'Label Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `label_${f.name}`,
                    label: `Label ${f.label}`
                }))
            },
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
                    },
                    {
                        name: 'gap',
                        type: 'range',
                        label: 'Gap',
                        min: 8,
                        max: 64,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    }
                ]
            },
            {
                id: 'itemStyle',
                label: 'Item Style',
                fields: [
                    {
                        name: 'itemBackgroundColor',
                        type: 'color',
                        label: 'Item Background'
                    },
                    {
                        name: 'itemBorderRadius',
                        type: 'range',
                        label: 'Border Radius',
                        min: 0,
                        max: 24,
                        step: 2,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'itemPadding',
                        type: 'range',
                        label: 'Item Padding',
                        min: 8,
                        max: 48,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    }
                ]
            },
            {
                id: 'premium_interactive',
                label: 'Interactive States',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Item Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1 },
                    { name: 'hover_brightness', type: 'range', label: 'Item Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 }
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

export default CountdownModule;
