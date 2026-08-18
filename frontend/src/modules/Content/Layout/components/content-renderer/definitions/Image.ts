import type { BlockDefinition } from '@/types/builder';
/**
 * Enhanced Image Block Definition
 * Display images with comprehensive effects, sizing, and styling
 */

import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import { defineAsyncComponent } from 'vue';
import { borderRadiusPresets } from './presets/border';
import { shadowOptions } from './presets/effects';

export default {
    name: 'image',
    label: 'Image',
    icon: ImageIcon,
    description: 'Display images with filters, effects, and responsive sizing.',
    category: 'media',
    component: defineAsyncComponent(() => import('@/shared/blocks/ImageBlock.vue')),

    settings: [
        // ============ CONTENT TAB ============
        { type: 'header', label: 'Image', tab: 'content' },
        {
            key: 'url',
            type: 'image',
            label: 'Image Source',
            default: '',
            tab: 'content'
        },
        {
            key: 'alt',
            type: 'text',
            label: 'Alt Text',
            default: '',
            placeholder: 'Describe the image for accessibility',
            tab: 'content'
        },
        {
            key: 'caption',
            type: 'text',
            label: 'Caption',
            default: '',
            placeholder: 'Optional image caption',
            tab: 'content'
        },

        { type: 'header', label: 'Link', tab: 'content' },
        {
            key: 'linkUrl',
            type: 'text',
            label: 'Link URL',
            default: '',
            placeholder: 'Make image clickable',
            tab: 'content'
        },
        {
            key: 'linkNewTab',
            type: 'boolean',
            label: 'Open in New Tab',
            default: false,
            tab: 'content'
        },

        // ============ STYLE TAB ============
        { type: 'header', label: 'Size', tab: 'style' },
        {
            key: 'width',
            type: 'slider',
            label: 'Width',
            min: 0,
            max: 1200,
            step: 50,
            unit: 'px',
            default: 0,
            responsive: true,
            tab: 'style'
        },
        {
            key: 'maxWidth',
            type: 'toggle_group',
            label: 'Max Width',
            options: [
                { label: 'Sm', value: 'max-w-sm' },
                { label: 'Md', value: 'max-w-xl' },
                { label: 'Lg', value: 'max-w-3xl' },
                { label: 'XL', value: 'max-w-5xl' },
                { label: 'Full', value: 'max-w-full' }
            ],
            default: 'max-w-full',
            tab: 'style'
        },
        {
            key: 'height',
            type: 'text',
            label: 'Height',
            default: 'auto',
            placeholder: 'auto, 300px, 50vh',
            tab: 'style'
        },
        {
            key: 'aspectRatio',
            type: 'select',
            label: 'Aspect Ratio',
            options: [
                { label: 'Auto', value: 'auto' },
                { label: '1:1 Square', value: '1/1' },
                { label: '4:3', value: '4/3' },
                { label: '3:2', value: '3/2' },
                { label: '16:9', value: '16/9' },
                { label: '21:9 Ultra Wide', value: '21/9' },
                { label: '3:4 Portrait', value: '3/4' },
                { label: '2:3 Portrait', value: '2/3' }
            ],
            default: 'auto',
            tab: 'style'
        },
        {
            key: 'objectFit',
            type: 'toggle_group',
            label: 'Object Fit',
            options: [
                { label: 'Cover', value: 'cover' },
                { label: 'Contain', value: 'contain' },
                { label: 'Fill', value: 'fill' },
                { label: 'None', value: 'none' }
            ],
            default: 'cover',
            tab: 'style'
        },
        {
            key: 'objectPosition',
            type: 'select',
            label: 'Object Position',
            options: [
                { label: 'Center', value: 'center' },
                { label: 'Top', value: 'top' },
                { label: 'Bottom', value: 'bottom' },
                { label: 'Left', value: 'left' },
                { label: 'Right', value: 'right' }
            ],
            default: 'center',
            tab: 'style'
        },

        { type: 'header', label: 'Alignment', tab: 'style' },
        {
            key: 'alignment',
            type: 'toggle_group',
            label: 'Image Alignment',
            options: [
                { label: 'Left', value: 'left', icon: AlignLeft },
                { label: 'Center', value: 'center', icon: AlignCenter },
                { label: 'Right', value: 'right', icon: AlignRight }
            ],
            default: 'center',
            responsive: true,
            tab: 'style'
        },

        { type: 'header', label: 'Border & Shadow', tab: 'style' },
        {
            key: 'radius',
            type: 'toggle_group',
            label: 'Border Radius',
            options: borderRadiusPresets,
            default: 'rounded-lg',
            tab: 'style'
        },
        {
            key: 'borderWidth',
            type: 'slider',
            label: 'Border Width',
            min: 0,
            max: 10,
            step: 1,
            unit: 'px',
            default: 0,
            tab: 'style'
        },
        {
            key: 'borderColor',
            type: 'color',
            label: 'Border Color',
            default: '#e5e7eb',
            tab: 'style'
        },
        {
            key: 'shadow',
            type: 'select',
            label: 'Shadow',
            options: shadowOptions,
            default: 'none',
            tab: 'style'
        },

        { type: 'header', label: 'Container', tab: 'style' },
        {
            key: 'padding',
            type: 'box_model',
            mode: 'padding',
            label: 'Padding',
            default: { top: '16px', right: '0', bottom: '16px', left: '0' },
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Container Background',
            default: 'transparent',
            tab: 'style'
        },

        // ============ ADVANCED TAB ============
        { type: 'header', label: 'Filters', tab: 'advanced' },
        {
            key: 'brightness',
            type: 'slider',
            label: 'Brightness',
            min: 0,
            max: 200,
            step: 5,
            default: 100,
            tab: 'advanced'
        },
        {
            key: 'contrast',
            type: 'slider',
            label: 'Contrast',
            min: 0,
            max: 200,
            step: 5,
            default: 100,
            tab: 'advanced'
        },
        {
            key: 'saturate',
            type: 'slider',
            label: 'Saturation',
            min: 0,
            max: 200,
            step: 5,
            default: 100,
            tab: 'advanced'
        },
        {
            key: 'blur',
            type: 'slider',
            label: 'Blur',
            min: 0,
            max: 20,
            step: 1,
            unit: 'px',
            default: 0,
            tab: 'advanced'
        },
        {
            key: 'grayscale',
            type: 'slider',
            label: 'Grayscale',
            min: 0,
            max: 100,
            step: 5,
            default: 0,
            tab: 'advanced'
        },

        { type: 'header', label: 'Hover Effects', tab: 'advanced' },
        {
            key: 'hoverEffect',
            type: 'select',
            label: 'Hover Effect',
            options: [
                { label: 'None', value: 'none' },
                { label: 'Zoom In', value: 'zoom' },
                { label: 'Zoom Out', value: 'zoom-out' },
                { label: 'Brighten', value: 'brighten' },
                { label: 'Grayscale to Color', value: 'colorize' },
                { label: 'Color to Grayscale', value: 'desaturate' },
                { label: 'Overlay', value: 'overlay' }
            ],
            default: 'none',
            tab: 'advanced'
        },
        {
            key: 'hoverOverlayColor',
            type: 'color',
            label: 'Hover Overlay Color',
            default: 'rgba(0, 0, 0, 0.3)',
            tab: 'advanced'
        },

        { type: 'header', label: 'Animation', tab: 'advanced' },
        {
            key: 'entranceAnimation',
            type: 'select',
            label: 'Entrance Animation',
            options: [
                { label: 'None', value: '' },
                { label: 'Fade In', value: 'animate-fade' },
                { label: 'Fade Up', value: 'animate-fade-up' },
                { label: 'Zoom In', value: 'animate-zoom' },
                { label: 'Slide Left', value: 'animate-slide-left' },
                { label: 'Slide Right', value: 'animate-slide-right' }
            ],
            default: 'animate-fade',
            tab: 'advanced'
        },
        {
            key: 'animationDuration',
            type: 'slider',
            label: 'Duration',
            min: 200,
            max: 2000,
            step: 100,
            unit: 'ms',
            default: 700,
            tab: 'advanced'
        },

        { type: 'header', label: 'Lazy Loading', tab: 'advanced' },
        {
            key: 'lazyLoad',
            type: 'boolean',
            label: 'Enable Lazy Loading',
            default: true,
            tab: 'advanced'
        }
    ],

    defaultSettings: {
        // Content
        url: '',
        alt: '',
        caption: '',
        linkUrl: '',
        linkNewTab: false,

        // Style
        width: 0,
        maxWidth: 'max-w-full',
        height: 'auto',
        aspectRatio: 'auto',
        objectFit: 'cover',
        objectPosition: 'center',
        alignment: 'center',
        radius: 'rounded-lg',
        borderWidth: 0,
        borderColor: '#e5e7eb',
        shadow: 'none',
        padding: { top: '16px', right: '0', bottom: '16px', left: '0' },
        bgColor: 'transparent',

        // Advanced
        brightness: 100,
        contrast: 100,
        saturate: 100,
        blur: 0,
        grayscale: 0,
        hoverEffect: 'none',
        hoverOverlayColor: 'rgba(0, 0, 0, 0.3)',
        entranceAnimation: 'animate-fade',
        animationDuration: 700,
        lazyLoad: true,

        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
