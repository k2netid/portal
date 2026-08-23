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
 * Sidebar Module Definition
 */
const SidebarModule: ModuleDefinition = {
    name: 'sidebar',
    title: 'Sidebar',
    icon: 'PanelRight',
    category: 'content',

    children: null,

    defaults: {
        showTitle: true,
        widgets: [
            { widgetType: 'search', title: 'Search' },
            { widgetType: 'categories', title: 'Categories', count: 5 }
        ],
        // Background
        background: { color: '#f9f9f9', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 24, bottom: 24, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 0, color: '#e0e0e0', style: 'solid' },
                top: { width: 0, color: '#e0e0e0', style: 'solid' },
                right: { width: 0, color: '#e0e0e0', style: 'solid' },
                bottom: { width: 0, color: '#e0e0e0', style: 'solid' },
                left: { width: 0, color: '#e0e0e0', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        aria_label: '',
        html_id: '',
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'widgets',
                label: 'Widgets',
                fields: [
                    { name: 'showTitle', type: 'toggle', label: 'Show Widget Titles', responsive: true },
                    {
                        name: 'widgets',
                        type: 'repeater',
                        label: 'Widgets',
                        itemLabel: 'title',
                        fields: [
                            {
                                name: 'widgetType',
                                type: 'select',
                                label: 'Widget Type',
                                options: [
                                    { value: 'search', label: 'Search Box' },
                                    { value: 'categories', label: 'Categories List' },
                                    { value: 'recentposts', label: 'Recent Posts' },
                                    { value: 'tags', label: 'Tag Cloud' },
                                    { value: 'text', label: 'Text / HTML' }
                                ]
                            },
                            { name: 'title', type: 'text', label: 'Title (Optional)', description: 'Leave empty to use default widget title.' },
                            {
                                name: 'count',
                                type: 'range',
                                label: 'Count',
                                min: 3,
                                max: 20,
                                step: 1,
                                show_if: { field: 'widgetType', value: ['categories', 'recentposts', 'tags'] }
                            },
                            {
                                name: 'content',
                                type: 'textarea',
                                label: 'Content',
                                show_if: { field: 'widgetType', value: 'text' }
                            }
                        ]
                    },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            layoutSettings,
            backgroundSettings,
            adminLabelSettings('Sidebar')
        ],
        design: [
            {
                id: 'typography',
                label: 'Widget Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `widget_${f.name}`,
                    label: `Widget ${f.label}`
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

export default SidebarModule;
