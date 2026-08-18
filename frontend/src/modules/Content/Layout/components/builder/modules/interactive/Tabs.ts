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
    layoutSettings,
} from '@/components/builder/modules/commonSettings';

/**
 * Tabs Module Definition
 */
const TabsModule: ModuleDefinition = {
    name: 'tabs',
    title: 'Tabs',
    icon: 'RectangleHorizontal',
    category: 'interactive',

    children: null, // Converted to repeater

    defaults: {
        items: [
            { title: 'Overview', content: '<p>Standardize your content with our powerful tab system. It supports rich text, images, and custom styling for each tab.</p>', icon: 'Layout' },
            { title: 'Features', content: '<p>Our tab system is highly responsive, ensuring your content looks great on any device.</p>', icon: 'Zap' },
            { title: 'Pricing', content: '<p>Flexible pricing plans tailored to your needs. Contact us for more information.</p>', icon: 'DollarSign' }
        ],
        // Layout
        tabPosition: 'top',
        tabAlignment: 'left',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 12, tr: 12, bl: 12, br: 12, linked: true },
            styles: { all: { width: 1, color: '#e0e0e0', style: 'solid' } }
        },
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
                id: 'tabs',
                label: 'Tabs',
                fields: [
                    {
                        name: 'items',
                        type: 'repeater',
                        label: 'Tabs',
                        itemLabel: 'title',
                        fields: [
                            { name: 'title', type: 'text', label: 'Tab Title' },
                            { name: 'icon', type: 'icon', label: 'Tab Icon' },
                            { name: 'content', type: 'richtext', label: 'Tab Content' }
                        ]
                    }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Tabs')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'tabPosition',
                        type: 'select',
                        label: 'Tab Position',
                        responsive: true,
                        options: [
                            { value: 'top', label: 'Top' },
                            { value: 'left', label: 'Left' },
                            { value: 'bottom', label: 'Bottom' }
                        ]
                    },
                    {
                        name: 'tabAlignment',
                        type: 'buttonGroup',
                        label: 'Tab Alignment',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' },
                            { value: 'fill', label: 'Fill', icon: 'Maximize2' }
                        ]
                    }
                ]
            },
            {
                id: 'tabStyle',
                label: 'Tab Style',
                fields: [
                    { name: 'tabBackgroundColor', type: 'color', label: 'Tab Background', responsive: true },
                    { name: 'tabActiveBackgroundColor', type: 'color', label: 'Active Tab Background', responsive: true }
                ]
            },
            {
                id: 'tabTypography',
                label: 'Tab Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `tab_${f.name}`,
                    label: `Tab ${f.label}`
                }))
            },
            {
                id: 'tabActiveTypography',
                label: 'Active Tab Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `tab_active_${f.name}`,
                    label: `Active Tab ${f.label}`
                }))
            },
            {
                id: 'contentStyle',
                label: 'Content Style',
                fields: [
                    { name: 'contentBackgroundColor', type: 'color', label: 'Content Background', responsive: true },
                    { name: 'contentPadding', type: 'spacing', label: 'Content Padding', responsive: true }
                ]
            },
            {
                id: 'contentTypography',
                label: 'Content Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `content_${f.name}`,
                    label: `Content ${f.label}`
                }))
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

export default TabsModule;
