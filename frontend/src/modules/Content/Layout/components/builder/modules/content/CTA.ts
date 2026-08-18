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
 * CTA (Call to Action) Module Definition
 */
const CTAModule: ModuleDefinition = {
    name: 'cta',
    title: 'Call to Action',
    icon: 'Megaphone',
    category: 'content',

    // No children
    children: null,

    // Default settings
    defaults: {
        // Content
        title: 'Ready to Transform Your Digital Experience?',
        content: 'Join over 10,000+ creators building world-class websites with our intuitive visual engine.',
        // Button
        buttonText: 'Get Started for Free',
        buttonUrl: '#',
        buttonTarget: '_self',
        // Layout
        layout: 'inline',
        alignment: 'center',
        // Colors
        gradientStart: '#4f46e5',
        gradientEnd: '#7c3aed',
        gradientDirection: 'to right',
        layout_type: 'block',
        gap_x: '0px',
        gap_y: '20px',
        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,
        background: { color: 'transparent', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        textColor: '#ffffff',
        buttonBackgroundColor: '#ffffff',
        buttonTextColor: '#4f46e5',
        // Title Typography Defaults
        title_font_size: 42,
        title_font_weight: '800',
        content_font_size: 18,
        // Spacing
        padding: { top: 80, bottom: 80, left: 60, right: 60, unit: 'px' },
        margin: { top: 40, bottom: 40, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 32, tr: 32, bl: 32, br: 32, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' }
            }
        },
        boxShadow: { preset: 'high', horizontal: 0, vertical: 25, blur: 50, spread: -12, color: 'rgba(0,0,0,0.25)', inset: false },
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
                id: 'button',
                label: 'Button',
                fields: [
                    {
                        name: 'buttonText',
                        type: 'text',
                        label: 'Button Text',
                        responsive: true
                    },
                    {
                        name: 'buttonUrl',
                        type: 'text',
                        label: 'Button URL',
                        responsive: true
                    },
                    {
                        name: 'buttonTarget',
                        type: 'select',
                        label: 'Link Target',
                        options: [
                            { value: '_self', label: 'Same Window' },
                            { value: '_blank', label: 'New Tab' }
                        ]
                    },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Call to Action')
        ],
        design: [
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    {
                        name: 'layout',
                        type: 'select',
                        label: 'Layout',
                        responsive: true,
                        options: [
                            { value: 'stacked', label: 'Stacked (Vertical)' },
                            { value: 'inline', label: 'Inline (Horizontal)' }
                        ]
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
                id: 'premium_interactive',
                label: 'Interactive States',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1 },
                    { name: 'hover_brightness', type: 'range', label: 'Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 }
                ]
            },
            {
                id: 'buttonStyle',
                label: 'Button Style',
                fields: [
                    {
                        name: 'buttonBackgroundColor',
                        type: 'color',
                        label: 'Button Background'
                    },
                    {
                        name: 'buttonTextColor',
                        type: 'color',
                        label: 'Button Text'
                    }
                ]
            },
            {
                id: 'buttonTypography',
                label: 'Button Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `button_${f.name}`,
                    label: `Button ${f.label}`
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
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
};

export default CTAModule;
