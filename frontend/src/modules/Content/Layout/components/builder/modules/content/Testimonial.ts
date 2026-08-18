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
 * Testimonial Module Definition
 */
const TestimonialModule: ModuleDefinition = {
    name: 'testimonial',
    title: 'Testimonial',
    icon: 'Quote',
    category: 'content',

    children: null,

    defaults: {
        content: '"This platform has completely transformed how we build our web presence. The attention to detail and ease of use is simply unparalleled for designers who care about performance."',
        authorName: 'Sarah Johnson',
        authorTitle: 'Product Director @ Techflow Creative',
        authorImage: '',
        // Layout
        layout: 'card',
        alignment: 'center',
        // Quote Icon
        showQuoteIcon: true,
        quoteIconColor: 'rgba(15, 23, 42, 0.05)',
        quoteIconSize: 64,
        quoteIconName: 'quote',
        // Background
        background: { color: 'transparent', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 60, bottom: 60, left: 40, right: 40, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },

        // Typography Defaults
        content_font_size: 20,
        content_font_style: 'italic',
        content_font_family: 'serif',
        content_color: '#0f172a',

        // Border
        border: {
            radius: { tl: 32, tr: 32, bl: 32, br: 32, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' }
            }
        },
        boxShadow: { preset: 'high', horizontal: 0, vertical: 20, blur: 50, spread: -10, color: 'rgba(0,0,0,0.15)', inset: false },
        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,
        animation_effect: 'animate-fade-up',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'testimonial',
                label: 'Testimonial',
                fields: [
                    {
                        name: 'content',
                        type: 'textarea',
                        label: 'Testimonial Text',
                        responsive: true
                    },
                    {
                        name: 'authorName',
                        type: 'text',
                        label: 'Author Name',
                        responsive: true
                    },
                    {
                        name: 'authorTitle',
                        type: 'text',
                        label: 'Author Title/Company',
                        responsive: true
                    },
                    {
                        name: 'authorImage',
                        type: 'upload',
                        label: 'Author Photo',
                        responsive: true
                    },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
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
            linkSettings,
            backgroundSettings,
            orderSettings,
            adminLabelSettings('Testimonial')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'layout',
                        type: 'select',
                        label: 'Layout Style',
                        responsive: true,
                        options: [
                            { value: 'default', label: 'Default' },
                            { value: 'card', label: 'Card' },
                            { value: 'minimal', label: 'Minimal' }
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
                id: 'quoteIcon',
                label: 'Quote Icon',
                fields: [
                    {
                        name: 'showQuoteIcon',
                        type: 'toggle',
                        label: 'Show Quote Icon',
                        responsive: true
                    },
                    {
                        name: 'quoteIconName',
                        type: 'icon',
                        label: 'Select Quote Icon',
                        responsive: true
                    },
                    {
                        name: 'quoteIconColor',
                        type: 'color',
                        label: 'Icon Color',
                        responsive: true
                    },
                    {
                        name: 'quoteIconSize',
                        type: 'range',
                        label: 'Icon Size',
                        min: 24,
                        max: 96,
                        step: 4,
                        unit: 'px',
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
                id: 'authorNameTypography',
                label: 'Author Name Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `author_name_${f.name}`,
                    label: `Author Name ${f.label}`
                }))
            },
            {
                id: 'authorTitleTypography',
                label: 'Author Title Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `author_title_${f.name}`,
                    label: `Author Title ${f.label}`
                }))
            },
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            filterSettings,
            transformSettings,
            animationSettings,
            layoutSettings
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

export default TestimonialModule;
