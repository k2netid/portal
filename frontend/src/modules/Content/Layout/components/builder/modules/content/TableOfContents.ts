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
 * Table of Contents Module Definition
 */
const TableOfContentsModule: ModuleDefinition = {
    name: 'tableofcontents',
    title: 'Table of Contents',
    icon: 'List',
    category: 'content',

    children: null,

    defaults: {
        title: 'Table of Contents',
        headingLevels: 'h2,h3',
        showNumbers: true,
        collapsible: false,
        defaultExpanded: true,
        // Background
        background: { color: '#f9f9f9', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 20, bottom: 20, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 24, left: 0, right: 0, unit: 'px' },
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
        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'settings',
                label: 'Settings',
                fields: [
                    { name: 'title', type: 'text', label: 'Title', responsive: true },
                    { name: 'headingLevels', type: 'text', label: 'Heading Levels (e.g., h2,h3)', responsive: true },
                    { name: 'showNumbers', type: 'toggle', label: 'Show Numbers', responsive: true },
                    { name: 'collapsible', type: 'toggle', label: 'Collapsible', responsive: true },
                    { name: 'defaultExpanded', type: 'toggle', label: 'Default Expanded', responsive: true },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            layoutSettings,
            backgroundSettings,
            adminLabelSettings('Table of Contents')
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
                id: 'linkTypography',
                label: 'Link Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `link_${f.name}`,
                    label: `Link ${f.label}`
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

export default TableOfContentsModule;
