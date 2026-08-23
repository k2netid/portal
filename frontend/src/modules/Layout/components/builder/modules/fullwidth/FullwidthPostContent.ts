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
    layoutSettings,
    adminLabelSettings,
} from '@/components/builder/modules/commonSettings';

/**
 * Fullwidth Post Content Module Definition
 */
const FullwidthPostContentModule: ModuleDefinition = {
    name: 'fullwidthpostcontent',
    title: 'Fullwidth Post Content',
    icon: 'FileText',
    category: 'fullwidth',

    children: null,

    defaults: {
        // Layout
        maxWidth: 1200,
        contentWidth: 'full',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        paragraphSpacing: 24,
        headingSpacing: 32,
        padding: { top: 40, bottom: 40, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
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
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'contentWidth', type: 'select', label: 'Content Width', options: [
                            { value: 'full', label: 'Full Width' },
                            { value: 'boxed', label: 'Boxed' }
                        ]
                    },
                    { name: 'maxWidth', type: 'range', label: 'Max Width', min: 800, max: 1600, step: 50, unit: 'px', responsive: true }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Fullwidth Post Content')
        ],
        design: [
            {
                id: 'typography',
                label: 'Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `text_${f.name}`,
                    label: `Text ${f.label}`
                }))
            },
            {
                id: 'headingTypography',
                label: 'Heading Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `heading_${f.name}`,
                    label: `Heading ${f.label}`
                }))
            },
            {
                id: 'linkTypography',
                label: 'Link Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `link_${f.name}`,
                    label: `Link ${f.label}`
                }))
            },
            {
                id: 'images',
                label: 'Images',
                fields: [
                    {
                        name: 'imageAlignment', type: 'buttonGroup', label: 'Alignment', options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    },
                    { name: 'imageBorderRadius', type: 'range', label: 'Border Radius', min: 0, max: 24, step: 2, unit: 'px' }
                ]
            },
            {
                id: 'blockquotes',
                label: 'Blockquotes',
                fields: [
                    { name: 'blockquoteBorderColor', type: 'color', label: 'Border Color' },
                    { name: 'blockquoteBackgroundColor', type: 'color', label: 'Background' }
                ]
            },
            {
                id: 'textSpacing',
                label: 'Text Spacing',
                fields: [
                    { name: 'paragraphSpacing', type: 'range', label: 'Paragraph Spacing', min: 12, max: 48, step: 4, unit: 'px' },
                    { name: 'headingSpacing', type: 'range', label: 'Heading Spacing', min: 16, max: 64, step: 4, unit: 'px' }
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

export default FullwidthPostContentModule;
