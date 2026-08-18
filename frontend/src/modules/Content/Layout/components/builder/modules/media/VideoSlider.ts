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
 * Video Slider Module Definition
 */
const VideoSliderModule: ModuleDefinition = {
    name: 'videoslider',
    title: 'Video Slider',
    icon: 'Film',
    category: 'media',

    children: null,

    defaults: {
        items: [
            { title: 'Nature Cinematic', type: 'youtube', videoId: 'aqz-KE-bpKQ', thumbnail: '' },
            { title: 'City Lights', type: 'vimeo', videoId: '446698547', thumbnail: '' }
        ],
        // Navigation
        showArrows: true,
        showDots: true,
        showThumbnails: true,
        thumbnailPosition: 'bottom',
        autoplay: false,
        autoplaySpeed: 5000,
        // Layout
        aspectRatio: '16:9',
        slidesPerView: 1,
        gap: 20,
        // Video Settings
        videoAutoplay: false,
        videoMuted: true,
        videoLoop: false,
        showControls: true,
        // Overlay
        showPlayButton: true,
        playButtonSize: 80,
        playButtonColor: '#ffffff',
        overlayColor: 'rgba(0,0,0,0.3)',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 12, tr: 12, bl: 12, br: 12, linked: true },
            styles: { all: { width: 0, color: '#333333', style: 'solid' } }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'videos',
                label: 'Videos',
                fields: [
                    {
                        name: 'items',
                        type: 'repeater',
                        label: 'Videos',
                        itemLabel: 'title',
                        fields: [
                            { name: 'title', type: 'text', label: 'Video Title' },
                            {
                                name: 'type',
                                type: 'select',
                                label: 'Type',
                                options: [
                                    { value: 'youtube', label: 'YouTube' },
                                    { value: 'vimeo', label: 'Vimeo' },
                                    { value: 'mp4', label: 'Self Hosted (MP4)' }
                                ]
                            },
                            { name: 'videoId', type: 'text', label: 'Video ID / URL' },
                            { name: 'thumbnail', type: 'image', label: 'Custom Thumbnail' }
                        ]
                    }
                ]
            },
            {
                id: 'videoSettings',
                label: 'Video Settings',
                fields: [
                    { name: 'videoAutoplay', type: 'toggle', label: 'Autoplay Video', responsive: true },
                    { name: 'videoMuted', type: 'toggle', label: 'Muted', responsive: true },
                    { name: 'videoLoop', type: 'toggle', label: 'Loop', responsive: true },
                    { name: 'showControls', type: 'toggle', label: 'Show Controls', responsive: true }
                ]
            },
            backgroundSettings,
            layoutSettings,
            adminLabelSettings('Video Slider')
        ],
        design: [
            {
                id: 'navigation',
                label: 'Navigation',
                fields: [
                    { name: 'showArrows', type: 'toggle', label: 'Show Arrows', responsive: true },
                    { name: 'showDots', type: 'toggle', label: 'Show Dots', responsive: true },
                    { name: 'showThumbnails', type: 'toggle', label: 'Show Thumbnails', responsive: true },
                    {
                        name: 'thumbnailPosition', type: 'select', label: 'Thumbnail Position', responsive: true, options: [
                            { value: 'bottom', label: 'Bottom' },
                            { value: 'left', label: 'Left' },
                            { value: 'right', label: 'Right' }
                        ],
                        show_if: { field: 'showThumbnails', value: true }
                    },
                    { name: 'autoplay', type: 'toggle', label: 'Autoplay Slider', responsive: true },
                    { name: 'autoplaySpeed', type: 'range', label: 'Slider Speed', min: 2000, max: 10000, step: 500, unit: 'ms', responsive: true, show_if: { field: 'autoplay', value: true } }
                ]
            },
            {
                id: 'layout_custom',
                label: 'Slider Layout',
                fields: [
                    {
                        name: 'aspectRatio', type: 'select', label: 'Aspect Ratio', responsive: true, options: [
                            { value: '16:9', label: '16:9' },
                            { value: '4:3', label: '4:3' },
                            { value: '21:9', label: '21:9' },
                            { value: '1:1', label: '1:1' }
                        ]
                    },
                    { name: 'slidesPerView', type: 'range', label: 'Slides Per View', min: 1, max: 4, step: 1, responsive: true },
                    { name: 'gap', type: 'range', label: 'Gap', min: 0, max: 50, step: 5, unit: 'px', responsive: true }
                ]
            },
            {
                id: 'overlay',
                label: 'Overlay',
                fields: [
                    { name: 'showPlayButton', type: 'toggle', label: 'Show Play Button', responsive: true },
                    { name: 'playButtonSize', type: 'range', label: 'Button Size', min: 40, max: 120, step: 10, unit: 'px', responsive: true, show_if: { field: 'showPlayButton', value: true } },
                    { name: 'playButtonColor', type: 'color', label: 'Button Color', responsive: true, show_if: { field: 'showPlayButton', value: true } },
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

export default VideoSliderModule;
