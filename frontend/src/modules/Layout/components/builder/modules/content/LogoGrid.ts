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
 * Logo Grid Module Definition
 */
const LogoGridModule: ModuleDefinition = {
    name: 'logogrid',
    title: 'Logo Grid',
    icon: 'Grid',
    category: 'content',

    children: null,

    defaults: {
        items: [
            { name: 'Company 1', image: '', url: '#' },
            { name: 'Company 2', image: '', url: '#' },
            { name: 'Company 3', image: '', url: '#' },
            { name: 'Company 4', image: '', url: '#' }
        ],
        showTitle: true,
        title: 'Trusted by over 1,000+ companies worldwide',
        // Layout
        columns: 4,
        gap: 40,
        logoSize: 140,
        // Visual
        grayscale: true,
        hoverColor: true,
        logoOpacity: 0.6,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 60, bottom: 60, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
            styles: { all: { width: 0, color: '#e2e8f0', style: 'solid' } }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        aria_label: '',
        html_id: '',
        hover_scale: 1.05,
        hover_brightness: 110,
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'logos',
                label: 'Logos',
                fields: [
                    {
                        name: 'items',
                        type: 'repeater',
                        label: 'Logos',
                        itemLabel: 'name',
                        fields: [
                            { name: 'name', type: 'text', label: 'Company Name' },
                            { name: 'image', type: 'image', label: 'Logo' },
                            { name: 'url', type: 'text', label: 'Link URL' }
                        ]
                    }
                ]
            },
            {
                id: 'header',
                label: 'Header',
                fields: [
                    { name: 'showTitle', type: 'toggle', label: 'Show Title', responsive: true },
                    { name: 'title', type: 'text', label: 'Title', responsive: true, show_if: { field: 'showTitle', value: true } }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Logo Grid')
        ],
        design: [
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    { name: 'columns', type: 'range', label: 'Columns', min: 1, max: 12, step: 1, responsive: true },
                    { name: 'gap', type: 'range', label: 'Gap', min: 0, max: 100, step: 4, unit: 'px', responsive: true },
                    { name: 'logoSize', type: 'range', label: 'Max Logo Width', min: 40, max: 400, step: 10, unit: 'px', responsive: true }
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
            {
                id: 'visuals',
                label: 'Logo Styling',
                fields: [
                    { name: 'grayscale', type: 'toggle', label: 'Grayscale', responsive: true },
                    { name: 'hoverColor', type: 'toggle', label: 'Color on Hover', responsive: true },
                    { name: 'logoOpacity', type: 'range', label: 'Opacity', min: 0.1, max: 1, step: 0.1, responsive: true }
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

export default LogoGridModule;
