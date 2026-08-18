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
 * Blog Module Definition
 */
const BlogModule: ModuleDefinition = {
    name: 'blog',
    title: 'Blog Posts',
    icon: 'FileText',
    category: 'dynamic',

    children: null,

    defaults: {
        itemsPerPage: 6,
        columns: 3,
        title: 'Blog Posts',
        showImage: true,
        showExcerpt: true,
        showDate: true,
        showAuthor: true,
        showCategory: true,
        // Filter
        category: '',
        orderBy: 'date',
        order: 'desc',
        // Layout
        layout: 'grid',
        gap: 24,
        // Image
        imageAspectRatio: '16:9',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },

        hover_scale: 1,
        hover_brightness: 100,

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
                id: 'settings',
                label: 'Settings',
                fields: [
                    { name: 'title', type: 'text', label: 'Title', responsive: true }
                ]
            },
            {
                id: 'query',
                label: 'Query',
                fields: [
                    {
                        name: 'itemsPerPage',
                        type: 'range',
                        label: 'Posts Per Page',
                        min: 1,
                        max: 24,
                        step: 1,
                        responsive: true
                    },
                    {
                        name: 'category',
                        type: 'text',
                        label: 'Category Filter',
                        responsive: true
                    },
                    {
                        name: 'orderBy',
                        type: 'select',
                        label: 'Order By',
                        options: [
                            { value: 'date', label: 'Date' },
                            { value: 'title', label: 'Title' },
                            { value: 'views', label: 'Views' }
                        ]
                    },
                    {
                        name: 'order',
                        type: 'select',
                        label: 'Order',
                        options: [
                            { value: 'desc', label: 'Descending' },
                            { value: 'asc', label: 'Ascending' }
                        ]
                    }
                ]
            },
            {
                id: 'display',
                label: 'Display',
                fields: [
                    {
                        name: 'showImage',
                        type: 'toggle',
                        label: 'Show Featured Image',
                        responsive: true
                    },
                    {
                        name: 'showExcerpt',
                        type: 'toggle',
                        label: 'Show Excerpt',
                        responsive: true
                    },
                    {
                        name: 'showDate',
                        type: 'toggle',
                        label: 'Show Date',
                        responsive: true
                    },
                    {
                        name: 'showAuthor',
                        type: 'toggle',
                        label: 'Show Author',
                        responsive: true
                    },
                    {
                        name: 'showCategory',
                        type: 'toggle',
                        label: 'Show Category',
                        responsive: true
                    },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Blog Posts')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'layout',
                        type: 'select',
                        label: 'Layout Style',
                        responsive: true,
                        options: [
                            { value: 'grid', label: 'Grid' },
                            { value: 'list', label: 'List' }
                        ]
                    },
                    {
                        name: 'columns',
                        type: 'range',
                        label: 'Columns',
                        min: 1,
                        max: 6,
                        step: 1,
                        responsive: true
                    },
                    {
                        name: 'gap',
                        type: 'range',
                        label: 'Gap',
                        min: 0,
                        max: 80,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'imageAspectRatio',
                        type: 'select',
                        label: 'Image Aspect Ratio',
                        responsive: true,
                        options: [
                            { value: '16:9', label: '16:9' },
                            { value: '4:3', label: '4:3' },
                            { value: '1:1', label: '1:1' },
                            { value: 'custom', label: 'Original' }
                        ]
                    }
                ]
            },
            {
                id: 'cardStyle',
                label: 'Card Style',
                fields: [
                    {
                        name: 'cardBackgroundColor',
                        type: 'color',
                        label: 'Card Background',
                        responsive: true
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
                id: 'categoryTypography',
                label: 'Category Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `category_${f.name}`,
                    label: `Category ${f.label}`
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
                id: 'excerptTypography',
                label: 'Excerpt Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `excerpt_${f.name}`,
                    label: `Excerpt ${f.label}`
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

export default BlogModule;
