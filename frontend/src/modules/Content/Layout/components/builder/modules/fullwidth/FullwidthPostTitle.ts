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
 * Fullwidth Post Title Module Definition
 */
const FullwidthPostTitleModule: ModuleDefinition = {
    name: 'fullwidthposttitle',
    title: 'Fullwidth Post Title',
    icon: 'Heading1',
    category: 'fullwidth',

    children: null,

    defaults: {
        tag: 'h1',
        // Layout
        style: 'default',
        showFeaturedImage: true,
        overlayColor: 'rgba(0,0,0,0.5)',
        height: 400,
        contentAlignment: 'center',
        contentPosition: 'center',
        // Meta
        showMeta: true,
        showAuthor: true,
        showDate: true,
        showCategories: true,
        showCommentCount: false,
        // Background
        background: { color: '#1a1a1a', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 80, bottom: 80, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
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
                id: 'title',
                label: 'Title',
                fields: [
                    {
                        name: 'tag', type: 'select', label: 'HTML Tag', options: [
                            { value: 'h1', label: 'H1' },
                            { value: 'h2', label: 'H2' },
                            { value: 'h3', label: 'H3' }
                        ]
                    },
                    {
                        name: 'style', type: 'select', label: 'Style', options: [
                            { value: 'default', label: 'Default' },
                            { value: 'parallax', label: 'Parallax' },
                            { value: 'centered', label: 'Centered' }
                        ]
                    }
                ]
            },
            {
                id: 'meta',
                label: 'Meta',
                fields: [
                    { name: 'showMeta', type: 'toggle', label: 'Show Meta' },
                    { name: 'showAuthor', type: 'toggle', label: 'Show Author' },
                    { name: 'showDate', type: 'toggle', label: 'Show Date' },
                    { name: 'showCategories', type: 'toggle', label: 'Show Categories' },
                    { name: 'showCommentCount', type: 'toggle', label: 'Show Comments' }
                ]
            },
            {
                id: 'featuredImage',
                label: 'Featured Image',
                fields: [
                    { name: 'showFeaturedImage', type: 'toggle', label: 'Show as Background' }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Fullwidth Post Title')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'height', type: 'range', label: 'Height', min: 200, max: 700, step: 50, unit: 'px', responsive: true },
                    {
                        name: 'contentAlignment', type: 'buttonGroup', label: 'Alignment', responsive: true, options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    },
                    {
                        name: 'contentPosition', type: 'select', label: 'Position', options: [
                            { value: 'top', label: 'Top' },
                            { value: 'center', label: 'Center' },
                            { value: 'bottom', label: 'Bottom' }
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
                id: 'metaTypography',
                label: 'Meta Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `meta_${f.name}`,
                    label: `Meta ${f.label}`
                }))
            },
            {
                id: 'overlay',
                label: 'Overlay',
                fields: [
                    { name: 'overlayColor', type: 'color', label: 'Overlay Color' },
                    { name: 'overlayOpacity', type: 'range', label: 'Overlay Opacity', min: 0, max: 100, step: 5, unit: '%' }
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

export default FullwidthPostTitleModule;
