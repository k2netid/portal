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
 * Contact Form Module Definition
 */
const ContactFormModule: ModuleDefinition = {
    name: 'contactform',
    title: 'Contact Form',
    icon: 'Mail',
    category: 'forms',

    children: ['*'],

    defaults: {
        title: 'Contact Us',
        description: "We'd love to hear from you. Send us a message!",
        buttonText: 'Send Message',
        successMessage: 'Thank you! Your message has been sent.',
        emailTo: '',
        fields: [
            { fieldId: 'name', type: 'text', label: 'Name', placeholder: 'Your Name', required: true, width: '100%' },
            { fieldId: 'email', type: 'email', label: 'Email', placeholder: 'Your Email', required: true, width: '100%' },
            { fieldId: 'message', type: 'textarea', label: 'Message', placeholder: 'Your Message', required: true, width: '100%' }
        ],
        // Background
        background: { color: '#ffffff', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 32, bottom: 32, left: 32, right: 32, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 1, color: '#e0e0e0', style: 'solid' },
                top: { width: 1, color: '#e0e0e0', style: 'solid' },
                right: { width: 1, color: '#e0e0e0', style: 'solid' },
                bottom: { width: 1, color: '#e0e0e0', style: 'solid' },
                left: { width: 1, color: '#e0e0e0', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 4, blur: 12, spread: 0, color: 'rgba(0,0,0,0.1)', inset: false },
        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'text',
                label: 'General',
                fields: [
                    { name: 'title', type: 'text', label: 'Title', responsive: true },
                    { name: 'description', type: 'text', label: 'Description', responsive: true },
                    { name: 'buttonText', type: 'text', label: 'Button Text', responsive: true },
                    { name: 'successMessage', type: 'text', label: 'Success Message', responsive: true }
                ]
            },
            {
                id: 'fields',
                label: 'Form Fields',
                fields: [
                    {
                        name: 'fields',
                        type: 'repeater',
                        label: 'Form Fields',
                        itemLabel: 'label',
                        fields: [
                            { name: 'fieldId', type: 'text', label: 'Field ID (Unique)', description: 'Used for form submission data key.' },
                            {
                                name: 'type',
                                type: 'select',
                                label: 'Type',
                                options: [
                                    { value: 'text', label: 'Text Input' },
                                    { value: 'email', label: 'Email' },
                                    { value: 'textarea', label: 'Message / Textarea' },
                                    { value: 'checkbox', label: 'Checkbox' },
                                    { value: 'radio', label: 'Radio Buttons' },
                                    { value: 'select', label: 'Select Dropdown' }
                                ]
                            },
                            { name: 'label', type: 'text', label: 'Label' },
                            { name: 'placeholder', type: 'text', label: 'Placeholder' },
                            { name: 'required', type: 'toggle', label: 'Required' },
                            {
                                name: 'width',
                                type: 'select',
                                label: 'Width',
                                options: [
                                    { value: '100%', label: 'Full Width (100%)' },
                                    { value: '50%', label: 'Half Width (50%)' },
                                    { value: '33.33%', label: 'One Third (33%)' }
                                ]
                            },
                            {
                                name: 'options',
                                type: 'textarea',
                                label: 'Options',
                                description: 'One option per line (for Select, Radio, Checkbox).',
                                show_if: { field: 'type', value: ['select', 'radio', 'checkbox'], operator: 'in' }
                            }
                        ]
                    }
                ]
            },
            {
                id: 'email',
                label: 'Email Settings',
                fields: [
                    { name: 'emailTo', type: 'text', label: 'Email To' }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Contact Form')
        ],
        design: [
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
                id: 'labelTypography',
                label: 'Label Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `label_${f.name}`,
                    label: `Label ${f.label}`
                }))
            },
            {
                id: 'fieldTypography',
                label: 'Field Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `field_${f.name}`,
                    label: `Field ${f.label}`
                }))
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
                id: 'buttonStyles',
                label: 'Button Style',
                fields: [
                    { name: 'buttonBackgroundColor', type: 'color', label: 'Button Background' },
                    { name: 'buttonTextColor', type: 'color', label: 'Button Text Color' }
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

export default ContactFormModule;
