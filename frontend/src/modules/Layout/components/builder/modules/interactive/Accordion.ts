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
    orderSettings,
    adminLabelSettings,
    linkSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Accordion Module Definition
 */
const AccordionModule: ModuleDefinition = {
    name: 'accordion',
    title: 'Accordion',
    icon: 'List',
    category: 'interactive',

    children: null, // Converted to repeater

    defaults: {
        items: [
            { title: 'What is our mission?', content: 'Our mission is to provide high-quality building blocks for modern web applications.', open: true },
            { title: 'How does it work?', content: 'Simple. You drag, you drop, you customize. No code required but fully extensible.' },
            { title: 'Is it responsive?', content: 'Absolutely. Every setting can be tuned for desktop, tablet, and mobile devices.' }
        ],
        allowMultiple: false,
        // Toggle Icon
        toggleIcon: 'chevron', // Simplified default
        iconPosition: 'right',
        iconColor: '',
        // Layout
        gap: 16,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        headerBackgroundColor: '#f8fafc',
        openHeaderBackgroundColor: '#f1f5f9',
        contentBackgroundColor: '#ffffff',
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 12, tr: 12, bl: 12, br: 12, linked: true },
            styles: { all: { width: 1, color: '#e2e8f0', style: 'solid' } }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'accordion',
                label: 'Accordion',
                fields: [
                    {
                        name: 'items',
                        type: 'repeater',
                        label: 'Accordions',
                        itemLabel: 'title',
                        fields: [
                            { name: 'title', type: 'text', label: 'Title' },
                            { name: 'content', type: 'richtext', label: 'Content' },
                            { name: 'open', type: 'toggle', label: 'Open by Default' }
                        ]
                    },
                    { name: 'allowMultiple', type: 'toggle', label: 'Allow Multiple Open', responsive: true }
                ]
            },
            {
                id: 'icon',
                label: 'Toggle Icon',
                fields: [
                    {
                        name: 'toggleIcon',
                        type: 'icon',
                        label: 'Icon',
                        default: 'chevron-down',
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
                    { name: 'iconColor', type: 'color', label: 'Icon Color', responsive: true },
                    { name: 'iconSize', type: 'range', label: 'Icon Size', min: 10, max: 40, step: 2, unit: 'px', responsive: true }
                ]
            },
            linkSettings,
            backgroundSettings,
            layoutSettings,
            orderSettings,
            adminLabelSettings('Accordion')
        ],
        design: [
            {
                id: 'accordionStyle',
                label: 'Accordion Style',
                fields: [
                    { name: 'gap', type: 'range', label: 'Item Gap', min: 0, max: 50, step: 2, unit: 'px', responsive: true },
                    { name: 'headerBackgroundColor', type: 'color', label: 'Header Background', responsive: true },
                    { name: 'openHeaderBackgroundColor', type: 'color', label: 'Open Header Background', responsive: true },
                    { name: 'contentBackgroundColor', type: 'color', label: 'Content Background', responsive: true }
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

export default AccordionModule;
