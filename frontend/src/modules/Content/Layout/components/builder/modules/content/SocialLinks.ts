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
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Social Links Module Definition
 */
const SocialLinksModule: ModuleDefinition = {
    name: 'sociallinks',
    title: 'Social Links',
    icon: 'Share2',
    category: 'content',

    children: null,

    defaults: {
        links: [
            { network: 'facebook', url: '#', iconColor: '#1877F2', backgroundColor: '#e7f3ff', useCustomColor: true },
            { network: 'twitter', url: '#', iconColor: '#1DA1F2', backgroundColor: '#e8f5fe', useCustomColor: true },
            { network: 'instagram', url: '#', iconColor: '#E4405F', backgroundColor: '#fef0f3', useCustomColor: true },
            { network: 'linkedin', url: '#', iconColor: '#0A66C2', backgroundColor: '#e7f0f7', useCustomColor: true }
        ],
        // Style
        displayStyle: 'icon-only',
        iconSize: 24,
        color: '#333333',
        hoverColor: '#2059ea',
        hoverBackgroundColor: '',
        // Layout
        alignment: 'center',
        gap: 16,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        aria_label: '',
        html_id: '',
        hover_scale: 1.1,
        hover_brightness: 110,
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'links',
                label: 'Social Links',
                fields: [
                    { name: 'showLabels', type: 'toggle', label: 'Show Labels' },
                    {
                        name: 'links',
                        type: 'repeater',
                        label: 'Social Networks',
                        itemLabel: 'network',
                        fields: [
                            {
                                name: 'network',
                                type: 'select',
                                label: 'Network',
                                options: [
                                    { value: 'facebook', label: 'Facebook' },
                                    { value: 'twitter', label: 'Twitter' },
                                    { value: 'instagram', label: 'Instagram' },
                                    { value: 'linkedin', label: 'LinkedIn' },
                                    { value: 'youtube', label: 'YouTube' },
                                    { value: 'github', label: 'GitHub' },
                                    { value: 'whatsapp', label: 'WhatsApp' },
                                    { value: 'email', label: 'Email' },
                                    { value: 'website', label: 'Website' }
                                ]
                            },
                            { name: 'url', type: 'text', label: 'Profile URL' },
                            { name: 'useCustomColor', type: 'toggle', label: 'Use Custom Color' },
                            { name: 'iconColor', type: 'color', label: 'Icon Color', show_if: { field: 'useCustomColor', value: true } },
                            { name: 'backgroundColor', type: 'color', label: 'Background Color', show_if: { field: 'useCustomColor', value: true } }
                        ]
                    },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Social Links')
        ],
        design: [
            {
                id: 'style',
                label: 'Style',
                fields: [
                    {
                        name: 'displayStyle',
                        type: 'select',
                        label: 'Display Style',
                        responsive: true,
                        options: [
                            { value: 'icon-only', label: 'Icon Only' },
                            { value: 'icon-circle', label: 'Icon with Circle' },
                            { value: 'icon-square', label: 'Icon with Square' },
                            { value: 'with-label', label: 'With Label' }
                        ]
                    },
                    {
                        name: 'iconSize',
                        type: 'range',
                        label: 'Icon Size',
                        min: 16,
                        max: 48,
                        step: 2,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'gap',
                        type: 'range',
                        label: 'Gap',
                        min: 8,
                        max: 64,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    }
                ]
            },
            {
                id: 'iconColors',
                label: 'Global Icon Colors',
                fields: [
                    { name: 'color', type: 'color', label: 'Icon Color', responsive: true },
                    { name: 'hoverColor', type: 'color', label: 'Hover Color', responsive: true },
                    { name: 'hoverBackgroundColor', type: 'color', label: 'Hover Background', responsive: true }
                ]
            },
            {
                ...layoutSettings,
                fields: [
                    ...(layoutSettings.fields as SettingDefinition[]),
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Alignment',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    },
                    {
                        name: 'gap',
                        type: 'range',
                        label: 'Gap',
                        min: 8,
                        max: 64,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    }
                ]
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

export default SocialLinksModule;
