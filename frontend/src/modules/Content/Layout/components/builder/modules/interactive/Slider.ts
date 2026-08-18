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
 * Slider Module Definition
 */
const SliderModule: ModuleDefinition = {
    name: 'slider',
    title: 'Slider',
    icon: 'Layers',
    category: 'interactive',

    children: null, // Converted to repeater

    defaults: {
        items: [
            {
                title: 'Premium Experience',
                content: 'Discover our new collections and special offers exclusively for our members.',
                image: '',
                buttonText: 'Shop Now',
                buttonUrl: '#',
                alignment: 'center'
            },
            {
                title: 'Innovative Design',
                content: 'We push the boundaries of design to create products that inspire and perform.',
                image: '',
                buttonText: 'Learn More',
                buttonUrl: '#',
                alignment: 'left'
            }
        ],
        // Behavior
        autoplay: true,
        autoplaySpeed: 5000,
        loop: true,
        pauseOnHover: true,
        // Navigation
        showArrows: true,
        showDots: true,
        slideTransition: 'fade',
        height: 500,
        // Overlay
        overlayEnabled: true,
        overlayColor: 'rgba(0,0,0,0.4)',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
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
                id: 'slides',
                label: 'Slides',
                fields: [
                    {
                        name: 'items',
                        type: 'repeater',
                        label: 'Slides',
                        itemLabel: 'title',
                        fields: [
                            { name: 'title', type: 'text', label: 'Title' },
                            { name: 'content', type: 'richtext', label: 'Content' },
                            { name: 'image', type: 'image', label: 'Background Image' },
                            { name: 'buttonText', type: 'text', label: 'Button Text' },
                            { name: 'buttonUrl', type: 'text', label: 'Button URL' },
                            {
                                name: 'alignment',
                                type: 'buttonGroup',
                                label: 'Alignment',
                                options: [
                                    { value: 'left', label: 'Left', icon: 'AlignLeft' },
                                    { value: 'center', label: 'Center', icon: 'AlignCenter' },
                                    { value: 'right', label: 'Right', icon: 'AlignRight' }
                                ]
                            }
                        ]
                    }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Slider')
        ],
        design: [
            {
                id: 'behavior',
                label: 'Behavior',
                fields: [
                    { name: 'autoplay', type: 'toggle', label: 'Autoplay', responsive: true },
                    { name: 'autoplaySpeed', type: 'range', label: 'Autoplay Speed', min: 1000, max: 10000, step: 500, unit: 'ms', responsive: true },
                    { name: 'loop', type: 'toggle', label: 'Loop', responsive: true },
                    { name: 'pauseOnHover', type: 'toggle', label: 'Pause on Hover', responsive: true }
                ]
            },
            {
                id: 'navigation',
                label: 'Navigation',
                fields: [
                    { name: 'showArrows', type: 'toggle', label: 'Show Arrows', responsive: true },
                    { name: 'showDots', type: 'toggle', label: 'Show Dots', responsive: true },
                    {
                        name: 'slideTransition',
                        type: 'select',
                        label: 'Transition',
                        responsive: true,
                        options: [
                            { value: 'slide', label: 'Slide' },
                            { value: 'fade', label: 'Fade' }
                        ]
                    }
                ]
            },
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'height', type: 'range', label: 'Height', min: 200, max: 1000, step: 50, unit: 'px', responsive: true }
                ]
            },
            {
                id: 'overlay',
                label: 'Overlay',
                fields: [
                    { name: 'overlayEnabled', type: 'toggle', label: 'Enable Overlay', responsive: true },
                    { name: 'overlayColor', type: 'color', label: 'Overlay Color', responsive: true }
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
                id: 'contentTypography',
                label: 'Content Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `content_${f.name}`,
                    label: `Content ${f.label}`
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

export default SliderModule;
