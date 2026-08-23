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
    layoutSettings,
    adminLabelSettings,
} from '@/components/builder/modules/commonSettings';

/**
 * Audio Player Module Definition
 */
const AudioModule: ModuleDefinition = {
    name: 'audio',
    title: 'Audio Player',
    icon: 'Music',
    category: 'media',

    children: null,

    defaults: {
        url: '',
        coverImage: '',
        trackName: 'Audio Track',
        artistName: '',
        // Options
        autoplay: false,
        loop: false,
        showDownload: false,
        // Background
        background: { color: '#1a1a1a', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 16, bottom: 16, left: 20, right: 20, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
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
                id: 'audio',
                label: 'Audio',
                fields: [
                    {
                        name: 'url',
                        type: 'upload',
                        label: 'Audio File',
                        allowedExtensions: ['mp3', 'wav', 'ogg', 'm4a']
                    },
                    {
                        name: 'coverImage',
                        type: 'upload',
                        label: 'Cover Image',
                        allowedExtensions: ['jpg', 'jpeg', 'png', 'webp'],
                        responsive: true
                    },
                    {
                        name: 'trackName',
                        type: 'text',
                        label: 'Track Name'
                    },
                    {
                        name: 'artistName',
                        type: 'text',
                        label: 'Artist Name'
                    }
                ]
            },
            {
                id: 'options',
                label: 'Options',
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
                        name: 'showDownload',
                        type: 'toggle',
                        label: 'Show Download Button'
                    }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Audio Player')
        ],
        design: [
            {
                id: 'playerStyle',
                label: 'Player Style',
                fields: [
                    {
                        name: 'accentColor',
                        type: 'color',
                        label: 'Accent Color',
                        responsive: true
                    }
                ]
            },
            {
                id: 'titleTypography',
                label: 'Title Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `trackName_${f.name}`,
                    label: `Track Name ${f.label}`
                }))
            },
            {
                id: 'artistTypography',
                label: 'Artist Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `artistName_${f.name}`,
                    label: `Artist ${f.label}`
                }))
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

export default AudioModule;
