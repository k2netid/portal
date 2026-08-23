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
 * Code Module Definition
 */
const CodeModule: ModuleDefinition = {
    name: 'code',
    title: 'Code',
    icon: 'Code',
    category: 'content',

    children: null,

    defaults: {
        code: '<div class="custom-element">\n  <p>Your HTML code here</p>\n</div>',
        language: 'html',
        showLineNumbers: true,
        showCopyButton: true,
        windowChrome: false,
        maxHeight: '',
        // Theme
        theme: 'dark',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'code',
                label: 'Code',
                fields: [
                    {
                        name: 'code',
                        type: 'textarea',
                        label: 'Code',
                        responsive: true
                    },
                    {
                        name: 'language',
                        type: 'select',
                        label: 'Language',
                        options: [
                            { value: 'html', label: 'HTML' },
                            { value: 'css', label: 'CSS' },
                            { value: 'javascript', label: 'JavaScript' },
                            { value: 'php', label: 'PHP' },
                            { value: 'python', label: 'Python' },
                            { value: 'json', label: 'JSON' }
                        ]
                    },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Code')
        ],
        design: [
            {
                id: 'display',
                label: 'Display',
                fields: [
                    {
                        name: 'showLineNumbers',
                        type: 'toggle',
                        label: 'Show Line Numbers',
                        responsive: true
                    },
                    {
                        name: 'showCopyButton',
                        type: 'toggle',
                        label: 'Show Copy Button',
                        responsive: true
                    },
                    {
                        name: 'windowChrome',
                        type: 'toggle',
                        label: 'Window Chrome',
                        responsive: true
                    },
                    {
                        name: 'maxHeight',
                        type: 'text',
                        label: 'Max Height (e.g. 400px)',
                        responsive: true
                    },
                    {
                        name: 'theme',
                        type: 'select',
                        label: 'Theme',
                        options: [
                            { value: 'dark', label: 'Dark' },
                            { value: 'light', label: 'Light' }
                        ],
                        responsive: true
                    }
                ]
            },
            {
                id: 'typography',
                label: 'Code Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `code_${f.name}`,
                    label: `Code ${f.label}`
                }))
            },
            layoutSettings,
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

export default CodeModule;
