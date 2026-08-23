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
 * Video Module Definition
 */
const VideoModule: ModuleDefinition = {
    name: 'video',
    title: 'Video',
    icon: 'Play',
    category: 'media',

    children: null,

    defaults: {
        url: '',
        // Poster Image
        posterImage: '',
        // Aspect Ratio
        aspectRatio: '16:9',
        // Controls
        autoplay: false,
        loop: false,
        muted: false,
        controls: true,
        // Alignment
        alignment: 'center',
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

        // Animation
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'video',
                label: 'Video',
                fields: [
                    {
                        name: 'url',
                        type: 'text',
                        label: 'Video URL',
                        placeholder: 'YouTube, Vimeo, or direct file URL',
                        responsive: true
                    },
                    {
                        name: 'posterImage',
                        type: 'upload',
                        label: 'Poster Image',
                        allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
                        responsive: true
                    }
                ]
            },
            {
                id: 'playback',
                label: 'Playback',
                fields: [
                    {
                        name: 'autoplay',
                        type: 'toggle',
                        label: 'Autoplay'
                    },
                    {
                        name: 'loop',
                        type: 'toggle',
                        label: 'Loop'
                    },
                    {
                        name: 'muted',
                        type: 'toggle',
                        label: 'Muted'
                    },
                    {
                        name: 'controls',
                        type: 'toggle',
                        label: 'Show Controls'
                    }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Video')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'aspectRatio',
                        type: 'select',
                        label: 'Aspect Ratio',
                        responsive: true,
                        options: [
                            { value: '16:9', label: '16:9 (Widescreen)' },
                            { value: '4:3', label: '4:3 (Standard)' },
                            { value: '1:1', label: '1:1 (Square)' },
                            { value: '9:16', label: '9:16 (Portrait)' },
                            { value: '21:9', label: '21:9 (Ultrawide)' }
                        ]
                    },
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

export default VideoModule;
