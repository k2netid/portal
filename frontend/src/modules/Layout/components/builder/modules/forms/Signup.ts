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
 * Signup Form Module Definition
 */
const SignupModule: ModuleDefinition = {
    name: 'signup',
    title: 'Signup Form',
    icon: 'UserPlus',
    category: 'forms',

    children: ['*'],

    defaults: {
        title: 'Create Account',
        subtitle: 'Join us today and get started',
        // Fields
        showName: true,
        namePlaceholder: 'Full Name',
        emailPlaceholder: 'Email Address',
        passwordPlaceholder: 'Password',
        confirmPasswordPlaceholder: 'Confirm Password',
        buttonText: 'Sign Up',
        // Terms
        showTerms: true,
        termsText: 'I agree to the Terms of Service',
        termsUrl: '#',
        // Login Link
        showLoginLink: true,
        loginText: 'Already have an account?',
        loginLinkText: 'Login',
        loginUrl: '#',
        // Background
        background: { color: '#ffffff', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 32, bottom: 32, left: 32, right: 32, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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
                label: 'Text',
                fields: [
                    { name: 'title', type: 'text', label: 'Title', responsive: true },
                    { name: 'subtitle', type: 'text', label: 'Subtitle', responsive: true },
                    { name: 'buttonText', type: 'text', label: 'Button Text', responsive: true }
                ]
            },
            {
                id: 'fields',
                label: 'Fields',
                fields: [
                    { name: 'showName', type: 'toggle', label: 'Show Name Field', responsive: true },
                    { name: 'namePlaceholder', type: 'text', label: 'Name Placeholder', responsive: true },
                    { name: 'emailPlaceholder', type: 'text', label: 'Email Placeholder', responsive: true },
                    { name: 'passwordPlaceholder', type: 'text', label: 'Password Placeholder', responsive: true }
                ]
            },
            {
                id: 'terms',
                label: 'Terms & Login',
                fields: [
                    { name: 'showTerms', type: 'toggle', label: 'Show Terms Checkbox', responsive: true },
                    { name: 'termsText', type: 'text', label: 'Terms Text', responsive: true },
                    { name: 'showLoginLink', type: 'toggle', label: 'Show Login Link', responsive: true },
                    { name: 'loginText', type: 'text', label: 'Login Text', responsive: true }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Signup Form')
        ],
        design: [
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
                id: 'titleTypography',
                label: 'Title Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
                }))
            },
            {
                id: 'inputTypography',
                label: 'Input Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `input_${f.name}`,
                    label: `Input ${f.label}`
                }))
            },
            {
                id: 'buttonStyle',
                label: 'Button Style',
                fields: [
                    { name: 'buttonBackgroundColor', type: 'color', label: 'Button Background', responsive: true },
                    ...(typographySettings.fields as SettingDefinition[]).map(f => ({
                        ...f,
                        name: `button_${f.name}`,
                        label: `Button ${f.label}`
                    }))
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

export default SignupModule;
