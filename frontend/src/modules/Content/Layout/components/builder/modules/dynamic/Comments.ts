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
 * Comments Module Definition
 */
const CommentsModule: ModuleDefinition = {
    name: 'comments',
    title: 'Comments',
    icon: 'MessageSquare',
    category: 'dynamic',

    children: null,

    defaults: {
        title: 'Comments',
        showForm: true,
        showAvatar: true,
        showReplyButton: true,
        // Form Labels
        namePlaceholder: 'Your Name',
        emailPlaceholder: 'Your Email',
        commentPlaceholder: 'Write a comment...',
        submitText: 'Post Comment',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 24, bottom: 24, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true }, styles: { all: { width: 1, color: '#e0e0e0', style: 'solid' }, top: { width: 1, color: '#e0e0e0', style: 'solid' }, right: { width: 1, color: '#e0e0e0', style: 'solid' }, bottom: { width: 1, color: '#e0e0e0', style: 'solid' }, left: { width: 1, color: '#e0e0e0', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        aria_label: '',
        html_id: '',
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'display',
                label: 'Display',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'showForm', type: 'toggle', label: 'Show Comment Form', responsive: true },
                    { name: 'showAvatar', type: 'toggle', label: 'Show Avatars', responsive: true },
                    { name: 'showReplyButton', type: 'toggle', label: 'Show Reply Button', responsive: true },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            {
                id: 'avatarSettings',
                label: 'Avatar Settings',
                fields: [
                    { name: 'avatarSize', type: 'range', label: 'Avatar Size', min: 20, max: 100, step: 2, unit: 'px', responsive: true }
                ]
            },
            {
                id: 'labels',
                label: 'Form Labels',
                fields: [
                    { name: 'namePlaceholder', type: 'text', label: 'Name Placeholder' },
                    { name: 'emailPlaceholder', type: 'text', label: 'Email Placeholder' },
                    { name: 'commentPlaceholder', type: 'text', label: 'Comment Placeholder' },
                    { name: 'submitText', type: 'text', label: 'Submit Button Text' }
                ],
                show_if: { field: 'showForm', value: true }
            },
            backgroundSettings,
            adminLabelSettings('Comments')
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
                id: 'textTypography',
                label: 'Text Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `text_${f.name}`,
                    label: `Text ${f.label}`
                }))
            },
            {
                id: 'metaTypography',
                label: 'Meta Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `meta_${f.name}`,
                    label: `Meta ${f.label}`
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
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
};

export default CommentsModule;
