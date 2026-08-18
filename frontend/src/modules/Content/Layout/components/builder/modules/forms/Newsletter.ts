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
    adminLabelSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Newsletter Module Definition
 */
const NewsletterModule: ModuleDefinition = {
    name: 'newsletter',
    title: 'Newsletter',
    icon: 'Mail',
    category: 'forms',

    children: ['*'],

    defaults: {
        title: 'Subscribe to our Newsletter',
        subtitle: 'Get the latest updates delivered to your inbox',
        placeholder: 'Enter your email',
        buttonText: 'Subscribe',
        layout: 'inline',
        // Success
        successMessage: 'Thank you for subscribing!',
        // Background
        background: { color: '#f5f5f5', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Styling Defaults (can be overridden by typography)
        inputBackgroundColor: '#ffffff',
        buttonBackgroundColor: '#2059ea',
        // Spacing
        padding: { top: 32, bottom: 32, left: 32, right: 32, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
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
                id: 'text',
                label: 'Text',
                fields: [
                    { name: 'title', type: 'text', label: 'Title', responsive: true },
                    { name: 'subtitle', type: 'text', label: 'Subtitle', responsive: true },
                    { name: 'placeholder', type: 'text', label: 'Placeholder', responsive: true },
                    { name: 'buttonText', type: 'text', label: 'Button Text', responsive: true },
                    { name: 'successMessage', type: 'text', label: 'Success Message', responsive: true }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Newsletter')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'layout', type: 'select', label: 'Layout', responsive: true, options: [
                            { value: 'inline', label: 'Inline' },
                            { value: 'stacked', label: 'Stacked' }
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
                id: 'subtitleTypography',
                label: 'Subtitle Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `subtitle_${f.name}`,
                    label: `Subtitle ${f.label}`
                }))
            },
            {
                id: 'inputStyle',
                label: 'Input Style',
                fields: [
                    { name: 'inputBackgroundColor', type: 'color', label: 'Input Background' },
                    ...(typographySettings.fields as SettingDefinition[]).map(f => ({
                        ...f,
                        name: `input_${f.name}`,
                        label: `Input ${f.label}`
                    }))
                ]
            },
            {
                id: 'buttonStyle',
                label: 'Button Style',
                fields: [
                    { name: 'buttonBackgroundColor', type: 'color', label: 'Button Background' },
                    ...(typographySettings.fields as SettingDefinition[]).map(f => ({
                        ...f,
                        name: `button_${f.name}`,
                        label: `Button ${f.label}`
                    }))
                ]
            },
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            {
                id: 'premium_interactive',
                label: 'Interactive States',
                fields: [
                    { name: 'hover_scale', type: 'range', label: 'Hover Scale', min: 0.8, max: 1.5, step: 0.05, default: 1 },
                    { name: 'hover_brightness', type: 'range', label: 'Hover Brightness', min: 50, max: 150, step: 10, unit: '%', default: 100 }
                ]
            },
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

export default NewsletterModule;
