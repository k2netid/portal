import type { ModuleDefinition, SettingDefinition } from '@/types/builder';
import {
    backgroundSettings,
    spacingSettings,
    borderSettings,
    boxShadowSettings,
    sizingSettings,
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
 * Login Module Definition
 */
const LoginModule: ModuleDefinition = {
    name: 'login',
    title: 'Login Form',
    icon: 'LogIn',
    category: 'forms',

    children: ['*'],

    defaults: {
        title: 'Login',
        subtitle: 'Enter your credentials to access your account',
        // Fields
        showLabels: true,
        usernameLabel: 'Email Address',
        passwordLabel: 'Password',
        usernamePlaceholder: 'name@example.com',
        passwordPlaceholder: '••••••••',
        buttonText: 'Sign In',
        // Options
        showRememberMe: true,
        showForgotPassword: true,
        forgotPasswordText: 'Forgot password?',
        forgotPasswordUrl: '#',
        // Redirect
        redirectUrl: '',

        // Background
        background: { color: '#ffffff', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },

        // Typography Defaults (Old flat structure being moved to settings fields)
        // Title
        title_fontFamily: 'inherit', title_fontSize: 24, title_fontWeight: 'bold', title_lineHeight: 1.2, title_letterSpacing: 0, title_color: '#333333', title_textAlign: 'center',
        // Subtitle
        subtitle_fontFamily: 'inherit', subtitle_fontSize: 16, subtitle_fontWeight: 'normal', subtitle_lineHeight: 1.5, subtitle_letterSpacing: 0, subtitle_color: '#666666', subtitle_textAlign: 'center',
        // Label
        label_fontFamily: 'inherit', label_fontSize: 14, label_fontWeight: 'normal', label_lineHeight: 1.5, label_letterSpacing: 0, label_color: '#333333', label_textAlign: 'left',
        // Input
        input_fontFamily: 'inherit', input_fontSize: 16, input_fontWeight: 'normal', input_lineHeight: 1.5, input_letterSpacing: 0, input_color: '#333333', input_textAlign: 'left',
        // Button
        button_fontFamily: 'inherit', button_fontSize: 16, button_fontWeight: 'bold', button_lineHeight: 1.5, button_letterSpacing: 0, button_color: '#ffffff', button_textAlign: 'center', button_backgroundColor: '#2059ea',

        // Spacing
        padding: { top: 32, bottom: 32, left: 32, right: 32, unit: 'px' },
        margin: { top: 30, bottom: 30, left: 0, right: 0, unit: 'px' },

        // Border
        border: { radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true }, styles: { all: { width: 1, color: '#e0e0e0', style: 'solid' } } },

        // Box Shadow
        boxShadow: { preset: 'none', horizontal: 0, vertical: 4, blur: 12, spread: 0, color: 'rgba(0,0,0,0.1)', inset: false },

        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,

        // Sizing
        width: '100%',
        height: 'auto',

        // Animation
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'text',
                label: 'Text',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'subtitle', type: 'text', label: 'Subtitle' },
                    { name: 'buttonText', type: 'text', label: 'Button Text' }
                ]
            },
            {
                id: 'fields',
                label: 'Fields',
                fields: [
                    { name: 'showLabels', type: 'toggle', label: 'Show Labels' },
                    { name: 'usernameLabel', type: 'text', label: 'Username Label', show_if: { field: 'showLabels', value: true } },
                    { name: 'usernamePlaceholder', type: 'text', label: 'Username Placeholder' },
                    { name: 'passwordLabel', type: 'text', label: 'Password Label', show_if: { field: 'showLabels', value: true } },
                    { name: 'passwordPlaceholder', type: 'text', label: 'Password Placeholder' }
                ]
            },
            {
                id: 'options',
                label: 'Options',
                fields: [
                    { name: 'showRememberMe', type: 'toggle', label: 'Show Remember Me' },
                    { name: 'showForgotPassword', type: 'toggle', label: 'Show Forgot Password' },
                    { name: 'forgotPasswordText', type: 'text', label: 'Forgot Password Text', show_if: { field: 'showForgotPassword', value: true } },
                    { name: 'forgotPasswordUrl', type: 'text', label: 'Forgot Password URL', show_if: { field: 'showForgotPassword', value: true } }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Login')
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
                id: 'subtitleTypography',
                label: 'Subtitle Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `subtitle_${f.name}`,
                    label: `Subtitle ${f.label}`
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
                id: 'inputTypography',
                label: 'Input Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `input_${f.name}`,
                    label: `Input ${f.label}`
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

export default LoginModule;
