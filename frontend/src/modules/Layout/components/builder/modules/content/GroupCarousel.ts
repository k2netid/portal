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
    attributesSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Group Carousel Module Definition
 * A carousel container for sliding through grouped content
 */
const GroupCarouselModule: ModuleDefinition = {
    name: 'groupcarousel',
    title: 'Group Carousel',
    icon: 'Layers',
    category: 'content',

    children: ['*'],

    defaults: {
        // Slides configuration
        slidesPerView: 1,
        slidesPerGroup: 1,
        gap: 20,
        loop: true,
        centeredSlides: false,
        // Navigation
        showArrows: true,
        showDots: true,
        arrowPosition: 'side',
        arrowStyle: 'circle',
        arrowColor: '#ffffff',
        arrowBackgroundColor: '#2059ea',
        dotsColor: '#2059ea',
        dotsActiveColor: '#1a47b8',
        // Autoplay
        autoplay: false,
        autoplaySpeed: 5000,
        pauseOnHover: true,
        // Effects
        effect: 'slide',
        speed: 500,
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
                id: 'slides',
                label: 'Slides',
                fields: [
                    { name: 'slidesPerView', type: 'range', label: 'Slides Per View', min: 1, max: 6, step: 1, responsive: true },
                    { name: 'slidesPerGroup', type: 'range', label: 'Slides Per Group', min: 1, max: 6, step: 1 },
                    { name: 'gap', type: 'range', label: 'Gap', min: 0, max: 60, step: 4, unit: 'px' },
                    { name: 'loop', type: 'toggle', label: 'Loop Slides' },
                    { name: 'centeredSlides', type: 'toggle', label: 'Centered' },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            {
                id: 'autoplay',
                label: 'Autoplay',
                fields: [
                    { name: 'autoplay', type: 'toggle', label: 'Autoplay' },
                    { name: 'autoplaySpeed', type: 'range', label: 'Speed', min: 2000, max: 10000, step: 500, unit: 'ms' },
                    { name: 'pauseOnHover', type: 'toggle', label: 'Pause on Hover' }
                ]
            },
            backgroundSettings,
            loopSettings,
            adminLabelSettings('Group Carousel')
        ],
        design: [
            {
                id: 'navigation',
                label: 'Navigation',
                fields: [
                    { name: 'showArrows', type: 'toggle', label: 'Show Arrows' },
                    { name: 'showDots', type: 'toggle', label: 'Show Dots' },
                    {
                        name: 'arrowPosition', type: 'select', label: 'Arrow Position', options: [
                            { value: 'side', label: 'Side' },
                            { value: 'bottom', label: 'Bottom' },
                            { value: 'inside', label: 'Inside' }
                        ]
                    },
                    {
                        name: 'arrowStyle', type: 'select', label: 'Arrow Style', options: [
                            { value: 'circle', label: 'Circle' },
                            { value: 'square', label: 'Square' },
                            { value: 'minimal', label: 'Minimal' }
                        ]
                    }
                ]
            },
            {
                id: 'carouselColors',
                label: 'Carousel Colors',
                fields: [
                    { name: 'arrowColor', type: 'color', label: 'Arrow Color' },
                    { name: 'arrowBackgroundColor', type: 'color', label: 'Arrow Background' },
                    { name: 'dotsColor', type: 'color', label: 'Dots Color' },
                    { name: 'dotsActiveColor', type: 'color', label: 'Active Dot Color' }
                ]
            },
            {
                id: 'effects',
                label: 'Effects',
                fields: [
                    {
                        name: 'effect', type: 'select', label: 'Effect', options: [
                            { value: 'slide', label: 'Slide' },
                            { value: 'fade', label: 'Fade' },
                            { value: 'cube', label: 'Cube' },
                            { value: 'coverflow', label: 'Coverflow' },
                            { value: 'flip', label: 'Flip' }
                        ]
                    },
                    { name: 'speed', type: 'range', label: 'Transition Speed', min: 200, max: 1500, step: 100, unit: 'ms' }
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
            attributesSettings
        ]
    }
};

export default GroupCarouselModule;
