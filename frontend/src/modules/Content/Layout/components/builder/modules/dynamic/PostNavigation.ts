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
 * Post Navigation Module Definition
 */
const PostNavigationModule: ModuleDefinition = {
    name: 'postnavigation',
    title: 'Post Navigation',
    icon: 'ArrowLeftRight',
    category: 'dynamic',

    children: null,

    defaults: {
        style: 'simple',
        showThumbnails: true,
        showLabels: true,
        prevLabel: 'Previous Post',
        nextLabel: 'Next Post',
        // Background
        background: { color: '#f9f9f9', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 24, bottom: 24, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true }, styles: { all: { width: 1, color: '#e0e0e0', style: 'solid' }, top: { width: 1, color: '#e0e0e0', style: 'solid' }, right: { width: 1, color: '#e0e0e0', style: 'solid' }, bottom: { width: 1, color: '#e0e0e0', style: 'solid' }, left: { width: 1, color: '#e0e0e0', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },

        hover_scale: 1,
        hover_brightness: 100,

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
                    { name: 'style', type: 'select', label: 'Style', responsive: true, options: [{ value: 'simple', label: 'Simple' }, { value: 'card', label: 'Card' }] },
                    { name: 'showThumbnails', type: 'toggle', label: 'Show Thumbnails', responsive: true },
                    { name: 'showLabels', type: 'toggle', label: 'Show Labels', responsive: true },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            {
                id: 'labels',
                label: 'Labels',
                fields: [
                    { name: 'prevLabel', type: 'text', label: 'Previous Label', show_if: { field: 'showLabels', value: true } },
                    { name: 'nextLabel', type: 'text', label: 'Next Label', show_if: { field: 'showLabels', value: true } }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Post Navigation')
        ],
        design: [
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
                id: 'titleTypography',
                label: 'Title Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
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

export default PostNavigationModule;
