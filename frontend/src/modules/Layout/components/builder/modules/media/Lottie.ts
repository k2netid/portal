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
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    layoutSettings,
    adminLabelSettings,
} from '@/components/builder/modules/commonSettings';

/**
 * Lottie Animation Module Definition
 */
const LottieModule: ModuleDefinition = {
    name: 'lottie',
    title: 'Lottie Animation',
    icon: 'Film',
    category: 'media',

    children: null,

    defaults: {
        // Source
        source: 'url',
        animationUrl: '',
        animationJson: '',
        // Playback
        autoplay: true,
        loop: true,
        speed: 1,
        direction: 'forward',
        // Trigger
        trigger: 'none',
        triggerOffset: 50,
        // Size
        width: 300,
        height: 300,
        maxWidth: 100,
        // Alignment
        alignment: 'center',
        // Interactivity
        hoverAction: 'none',
        clickAction: 'none',
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
                id: 'source',
                label: 'Animation Source',
                fields: [
                    {
                        name: 'source', type: 'select', label: 'Source', options: [
                            { value: 'url', label: 'URL' },
                            { value: 'json', label: 'JSON' }
                        ]
                    },
                    { name: 'animationUrl', type: 'text', label: 'Animation URL', show_if: { field: 'source', value: 'url' } },
                    { name: 'animationJson', type: 'textarea', label: 'Animation JSON', show_if: { field: 'source', value: 'json' } }
                ]
            },
            {
                id: 'playback',
                label: 'Playback',
                fields: [
                    { name: 'autoplay', type: 'toggle', label: 'Autoplay' },
                    { name: 'loop', type: 'toggle', label: 'Loop' },
                    { name: 'speed', type: 'range', label: 'Speed', min: 0.1, max: 3, step: 0.1 },
                    {
                        name: 'direction', type: 'select', label: 'Direction', options: [
                            { value: 'forward', label: 'Forward' },
                            { value: 'reverse', label: 'Reverse' }
                        ]
                    }
                ]
            },
            {
                id: 'trigger',
                label: 'Trigger',
                fields: [
                    {
                        name: 'trigger', type: 'select', label: 'Play On', options: [
                            { value: 'none', label: 'Load' },
                            { value: 'scroll', label: 'Scroll' },
                            { value: 'hover', label: 'Hover' },
                            { value: 'click', label: 'Click' },
                            { value: 'viewport', label: 'In Viewport' }
                        ]
                    },
                    { name: 'triggerOffset', type: 'range', label: 'Trigger Offset', min: 0, max: 100, step: 5, unit: '%', show_if: { field: 'trigger', value: 'scroll' } }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Lottie Animation')
        ],
        design: [
            {
                id: 'alignment',
                label: 'Alignment',
                fields: [
                    {
                        name: 'alignment', type: 'buttonGroup', label: 'Alignment', responsive: true, options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    }
                ]
            },
            {
                id: 'interactivity',
                label: 'Interactivity',
                fields: [
                    {
                        name: 'hoverAction', type: 'select', label: 'On Hover', options: [
                            { value: 'none', label: 'None' },
                            { value: 'play', label: 'Play' },
                            { value: 'pause', label: 'Pause' },
                            { value: 'reverse', label: 'Reverse' }
                        ]
                    },
                    {
                        name: 'clickAction', type: 'select', label: 'On Click', options: [
                            { value: 'none', label: 'None' },
                            { value: 'toggle', label: 'Toggle Play/Pause' },
                            { value: 'restart', label: 'Restart' }
                        ]
                    }
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

export default LottieModule;
