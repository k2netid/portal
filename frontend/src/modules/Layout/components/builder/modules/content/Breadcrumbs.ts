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
    orderSettings,
    adminLabelSettings,
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Breadcrumbs Module Definition
 */
const BreadcrumbsModule: ModuleDefinition = {
    name: 'breadcrumbs',
    title: 'Breadcrumbs',
    icon: 'ChevronRight',
    category: 'content',

    children: null,

    defaults: {
        items: [
            { text: 'Home', url: '/' },
            { text: 'Blog', url: '/blog' },
            { text: 'Current Page', url: '' }
        ],
        separator: '/',
        showHome: true,
        homeIcon: true,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 12, bottom: 12, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' },
                top: { width: 0, color: '#333333', style: 'solid' },
                right: { width: 0, color: '#333333', style: 'solid' },
                bottom: { width: 0, color: '#333333', style: 'solid' },
                left: { width: 0, color: '#333333', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'items',
                label: 'Breadcrumbs',
                fields: [
                    { name: 'items', type: 'textarea', label: 'Items (JSON)', description: '[{text, url}]' },
                    { name: 'separator', type: 'text', label: 'Separator' },
                    { name: 'showHome', type: 'toggle', label: 'Show Home' },
                    { name: 'homeIcon', type: 'toggle', label: 'Use Home Icon' },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            orderSettings,
            adminLabelSettings('Breadcrumbs')
        ],
        design: [
            layoutSettings,
            {
                id: 'linksTypography',
                label: 'Links Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `links_${f.name}`,
                    label: `Link ${f.label}`
                }))
            },
            {
                id: 'activeTypography',
                label: 'Active Item Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `active_${f.name}`,
                    label: `Active ${f.label}`
                }))
            },
            {
                id: 'separatorStyle',
                label: 'Separator Style',
                fields: [
                    { name: 'separatorColor', type: 'color', label: 'Separator Color' }
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
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
};

export default BreadcrumbsModule;
