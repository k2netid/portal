import type { ModuleDefinition } from '@/types/builder';
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
    linkSettings,
    layoutSettings,
    loopSettings,
    orderSettings,
    adminLabelSettings,
    cssSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Row Module Definition
 */
const RowModule: ModuleDefinition = {
    name: 'row',
    title: 'Row',
    icon: 'Columns',
    category: 'structure',

    // Allowed children
    children: ['column'],

    // Parent constraint
    parent: ['section'],

    // Default settings
    defaults: {
        columns: '1-1', // Column structure: 1, 1-1, 1-1-1, 2-1, 1-2, etc
        gutterWidth: 30,
        equalizeColumns: true, // Refined to true for Divi behavior
        maxWidth: '100%',     // Refined to 100% for Divi behavior
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 30, bottom: 30, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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

        aria_label: '',
        html_id: '',
        hover_scale: 1,
        hover_brightness: 100,

        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1',
        link_target: '_self'
    },

    // Settings panel configuration
    settings: {
        content: [
            {
                id: 'elements',
                label: 'Elements',
                fields: [
                    {
                        name: 'column_manager',
                        type: 'children_manager',
                        label: ''
                    }
                ]
            },
            linkSettings,
            backgroundSettings,
            loopSettings,
            orderSettings,
            adminLabelSettings('Row')
        ],
        design: [
            layoutSettings,
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
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
}

export default RowModule;
