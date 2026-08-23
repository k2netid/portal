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
 * Fullwidth Post Slider Module Definition
 */
const FullwidthPostSliderModule: ModuleDefinition = {
    name: 'fullwidthpostslider',
    title: 'Fullwidth Post Slider',
    icon: 'Layers',
    category: 'fullwidth',

    children: null,

    defaults: {
        // Content
        postsPerPage: 5,
        postType: 'post',
        categories: [],
        orderBy: 'date',
        order: 'desc',
        showExcerpt: true,
        excerptLength: 150,
        showMeta: true,
        showAuthor: true,
        showDate: true,
        showCategories: true,
        showReadMore: true,
        readMoreText: 'Read More',
        // Navigation
        showArrows: true,
        showDots: true,
        autoplay: true,
        autoplaySpeed: 5000,
        pauseOnHover: true,
        // Layout
        height: 600,
        contentAlignment: 'center',
        contentPosition: 'center',
        overlayGradient: true,
        // Button
        buttonStyle: 'solid',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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
                id: 'query',
                label: 'Content',
                fields: [
                    {
                        name: 'postType', type: 'select', label: 'Post Type', options: [
                            { value: 'post', label: 'Posts' },
                            { value: 'page', label: 'Pages' },
                            { value: 'portfolio', label: 'Portfolio' }
                        ]
                    },
                    { name: 'postsPerPage', type: 'range', label: 'Number of Posts', min: 1, max: 10, step: 1 },
                    { name: 'categories', type: 'text', label: 'Categories (comma separated)' },
                    {
                        name: 'orderBy', type: 'select', label: 'Order By', options: [
                            { value: 'date', label: 'Date' },
                            { value: 'title', label: 'Title' },
                            { value: 'rand', label: 'Random' },
                            { value: 'comment_count', label: 'Comment Count' }
                        ]
                    },
                    {
                        name: 'order', type: 'select', label: 'Order', options: [
                            { value: 'desc', label: 'Descending' },
                            { value: 'asc', label: 'Ascending' }
                        ]
                    }
                ]
            },
            {
                id: 'elements',
                label: 'Elements',
                fields: [
                    { name: 'showExcerpt', type: 'toggle', label: 'Show Excerpt' },
                    { name: 'excerptLength', type: 'range', label: 'Excerpt Length', min: 50, max: 300, step: 10 },
                    { name: 'showMeta', type: 'toggle', label: 'Show Meta' },
                    { name: 'showAuthor', type: 'toggle', label: 'Show Author' },
                    { name: 'showDate', type: 'toggle', label: 'Show Date' },
                    { name: 'showCategories', type: 'toggle', label: 'Show Categories' },
                    { name: 'showReadMore', type: 'toggle', label: 'Show Read More' },
                    { name: 'readMoreText', type: 'text', label: 'Read More Text' }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Fullwidth Post Slider')
        ],
        design: [
            {
                id: 'navigation',
                label: 'Navigation',
                fields: [
                    { name: 'showArrows', type: 'toggle', label: 'Show Arrows' },
                    { name: 'showDots', type: 'toggle', label: 'Show Dots' },
                    { name: 'autoplay', type: 'toggle', label: 'Autoplay' },
                    { name: 'autoplaySpeed', type: 'range', label: 'Speed', min: 2000, max: 10000, step: 500, unit: 'ms' },
                    { name: 'pauseOnHover', type: 'toggle', label: 'Pause on Hover' }
                ]
            },
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'height', type: 'range', label: 'Height', min: 400, max: 900, step: 50, unit: 'px', responsive: true },
                    {
                        name: 'contentAlignment', type: 'buttonGroup', label: 'Alignment', options: [
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
                id: 'overlay',
                label: 'Overlay',
                fields: [
                    { name: 'overlayColor', type: 'color', label: 'Overlay Color' },
                    { name: 'overlayGradient', type: 'toggle', label: 'Gradient Overlay' }
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
                id: 'excerptTypography',
                label: 'Excerpt Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `excerpt_${f.name}`,
                    label: `Excerpt ${f.label}`
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
                id: 'buttonTypography',
                label: 'Button Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `button_${f.name}`,
                    label: `Button ${f.label}`
                }))
            },
            {
                id: 'buttonStyle',
                label: 'Button Style',
                fields: [
                    {
                        name: 'buttonStyle', type: 'select', label: 'Style', options: [
                            { value: 'solid', label: 'Solid' },
                            { value: 'outline', label: 'Outline' },
                            { value: 'text', label: 'Text' }
                        ]
                    },
                    { name: 'buttonBackgroundColor', type: 'color', label: 'Background' }
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

export default FullwidthPostSliderModule;
