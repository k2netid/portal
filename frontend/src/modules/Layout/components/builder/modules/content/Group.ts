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
    cssSettings,
    loopSettings,
    orderSettings,
    adminLabelSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Group Module Definition
 * A container for grouping multiple modules together
 */
const GroupModule: ModuleDefinition = {
    name: 'group',
    title: 'Group',
    icon: 'Square',
    category: 'content',

    children: ['*'],

    defaults: {
        // Layout
        direction: 'column',
        alignItems: 'stretch',
        justifyContent: 'flex-start',
        gap: 20,
        wrap: false,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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
        // Link
        link_url: '',
        link_target: '_self',
        aria_label: '',
        html_id: '',
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'elements',
                label: 'Elements',
                fields: [
                    {
                        name: 'module_manager',
                        type: 'children_manager',
                        label: ''
                    }
                ]
            },
            {
                id: 'link',
                label: 'Link',
                fields: [
                    { name: 'link_url', type: 'text', label: 'URL' },
                    {
                        name: 'link_target', type: 'select', label: 'Target', options: [
                            { value: '_self', label: 'Same Window' },
                            { value: '_blank', label: 'New Window' }
                        ]
                    }
                ]
            },
            backgroundSettings,
            loopSettings,
            adminLabelSettings('Group')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'direction', type: 'select', label: 'Direction', options: [
                            { value: 'column', label: 'Vertical' },
                            { value: 'row', label: 'Horizontal' }
                        ]
                    },
                    {
                        name: 'alignItems', type: 'select', label: 'Align Items', options: [
                            { value: 'flex-start', label: 'Start' },
                            { value: 'center', label: 'Center' },
                            { value: 'flex-end', label: 'End' },
                            { value: 'stretch', label: 'Stretch' }
                        ]
                    },
                    {
                        name: 'justifyContent', type: 'select', label: 'Justify Content', options: [
                            { value: 'flex-start', label: 'Start' },
                            { value: 'center', label: 'Center' },
                            { value: 'flex-end', label: 'End' },
                            { value: 'space-between', label: 'Space Between' },
                            { value: 'space-around', label: 'Space Around' }
                        ]
                    },
                    { name: 'gap', type: 'range', label: 'Gap', min: 0, max: 60, step: 4, unit: 'px', responsive: true },
                    { name: 'wrap', type: 'toggle', label: 'Wrap Items' }
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
            orderSettings,
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            layoutSettings,
            attributesSettings
        ]
    }
};

export default GroupModule;
