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
 * Blurb Module Definition
 * Icon/Image + Title + Content layout
 */
const BlurbModule: ModuleDefinition = {
    name: 'blurb',
    title: 'Blurb',
    icon: 'LayoutList',
    category: 'content',

    // No children
    children: null,

    // Default settings
    defaults: {
        // Content
        title: 'Lightning Performance',
        content: 'Optimized for the modern web with blazing fast load times and optimized asset delivery.',
        // Media Type
        mediaType: 'icon',
        iconName: 'Zap',
        image: '',
        // Layout
        iconPosition: 'top',
        alignment: 'center',
        // Icon Styling
        iconSize: 32,
        iconColor: '#4f46e5',
        iconBackgroundColor: 'rgba(79, 70, 229, 0.08)',
        iconBackgroundShape: 'rounded',
        layout_type: 'block',
        gap_x: '0px',
        gap_y: '0px',
        aria_label: '',
        html_id: '',
        hover_scale: 1.05,
        hover_brightness: 100,
        // Typography Defaults
        title_font_size: 20,
        title_font_weight: '700',
        content_color: '#64748b',
        // Spacing
        padding: { top: 32, bottom: 32, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Background
        background: { color: 'transparent', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Border
        border: {
            radius: { tl: 24, tr: 24, bl: 24, br: 24, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' }
            }
        },
        boxShadow: { preset: 'medium', horizontal: 0, vertical: 10, blur: 30, spread: -5, color: 'rgba(0,0,0,0.05)', inset: false },
        // Animation
        animation_effect: 'animate-fade-up',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    // Settings panel configuration
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
                        type: 'textarea',
                        label: 'Content',
                        responsive: true
                    }
                ]
            },
            {
                id: 'media',
                label: 'Media',
                fields: [
                    {
                        name: 'mediaType',
                        type: 'buttonGroup',
                        label: 'Media Type',
                        options: [
                            { value: 'icon', label: 'Icon' },
                            { value: 'image', label: 'Image' },
                            { value: 'none', label: 'None' }
                        ]
                    },
                    {
                        name: 'iconName',
                        type: 'icon',
                        label: 'Select Icon',
                        responsive: true
                    },
                    {
                        name: 'image',
                        type: 'upload',
                        label: 'Image',
                        responsive: true
                    },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            linkSettings,
            backgroundSettings,
            orderSettings,
            adminLabelSettings('Blurb')
        ],
        design: [
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    {
                        name: 'iconPosition',
                        type: 'select',
                        label: 'Icon/Image Position',
                        responsive: true,
                        options: [
                            { value: 'top', label: 'Top' },
                            { value: 'left', label: 'Left' },
                            { value: 'right', label: 'Right' }
                        ]
                    },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Text Alignment',
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
                id: 'premium_styling',
                label: 'Premium Interactive',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1.05 },
                    { name: 'hover_brightness', type: 'range', label: 'Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 }
                ]
            },
            {
                id: 'iconStyle',
                label: 'Icon Style',
                fields: [
                    {
                        name: 'iconSize',
                        type: 'range',
                        label: 'Icon Size',
                        min: 24,
                        max: 128,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'iconColor',
                        type: 'color',
                        label: 'Icon Color',
                        responsive: true
                    },
                    {
                        name: 'iconBackgroundColor',
                        type: 'color',
                        label: 'Icon Background',
                        responsive: true
                    },
                    {
                        name: 'iconBackgroundShape',
                        type: 'select',
                        label: 'Icon Background Shape',
                        responsive: true,
                        options: [
                            { value: 'none', label: 'None' },
                            { value: 'circle', label: 'Circle' },
                            { value: 'square', label: 'Square' },
                            { value: 'rounded', label: 'Rounded' }
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
                id: 'contentTypography',
                label: 'Content Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `content_${f.name}`,
                    label: `Content ${f.label}`
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

export default BlurbModule;
