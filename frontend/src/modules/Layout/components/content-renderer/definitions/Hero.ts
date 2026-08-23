import type { BlockDefinition } from '@/types/builder';
/**
 * Enhanced Hero Block Definition
 * Hero header with comprehensive styling options for each sub-component
 */

import LayoutTemplate from 'lucide-vue-next/dist/esm/icons/layout-template.js';
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import { defineAsyncComponent } from 'vue';

// Import reusable presets
import {
    fontFamilyOptions,
    fontWeightOptions
} from './presets/typography';

import {
    bgSizeOptions,
    bgPositionOptions,
    gradientDirectionOptions
} from './presets/background';

import { borderRadiusPresets } from './presets/border';

export default {
    name: 'hero',
    label: 'Hero Header',
    icon: LayoutTemplate,
    description: 'Large hero banner with title, subtitle, and CTA buttons.',
    category: 'content',
    component: defineAsyncComponent(() => import('@/shared/blocks/HeroBlock.vue')),

    settings: [
        // ============ CONTENT TAB ============
        { type: 'header', label: 'Title', tab: 'content' },
        {
            key: 'title',
            type: 'text',
            label: 'Title Text',
            default: 'New Hero Header',
            tab: 'content'
        },
        {
            key: 'titleTag',
            type: 'select',
            label: 'HTML Tag',
            options: [
                { label: 'H1', value: 'h1' },
                { label: 'H2', value: 'h2' },
                { label: 'H3', value: 'h3' },
                { label: 'Div', value: 'div' }
            ],
            default: 'h1',
            tab: 'content'
        },

        {
            key: 'subtitle',
            type: 'textarea',
            label: 'Subtitle Text',
            default: 'Experience the next generation of visual editing.',
            tab: 'content'
        },
        {
            key: 'eyebrow',
            type: 'text',
            label: 'Eyebrow / Badge Text',
            default: '',
            tab: 'content'
        },
        {
            key: 'layout',
            type: 'select',
            label: 'Layout Variant',
            options: [
                { label: 'Centered', value: 'centered' },
                { label: 'Split (Image Right)', value: 'split' }
            ],
            default: 'centered',
            tab: 'content'
        },
        {
            key: 'image',
            type: 'image',
            label: 'Split Layout Image',
            showIf: { key: 'layout', value: 'split' },
            tab: 'content'
        },
        {
            key: 'useGlass',
            type: 'boolean',
            label: 'Use Glassmorphism',
            default: false,
            tab: 'content'
        },

        // ============ STYLE TAB ============
        { type: 'header', label: 'Title Style', tab: 'style' },
        {
            key: 'titleFontFamily',
            type: 'select',
            label: 'Font Family',
            options: fontFamilyOptions,
            default: 'inherit',
            tab: 'style'
        },
        {
            key: 'titleSize',
            type: 'slider',
            label: 'Font Size',
            min: 24,
            max: 120,
            step: 2,
            unit: 'px',
            default: 56,
            responsive: true,
            tab: 'style'
        },
        {
            key: 'titleWeight',
            type: 'toggle_group',
            label: 'Font Weight',
            options: fontWeightOptions,
            default: '700',
            tab: 'style'
        },
        {
            key: 'titleAlign',
            type: 'toggle_group',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left', icon: AlignLeft },
                { label: 'Center', value: 'center', icon: AlignCenter },
                { label: 'Right', value: 'right', icon: AlignRight }
            ],
            default: 'center',
            responsive: true,
            tab: 'style'
        },
        {
            key: 'titleColor',
            type: 'color',
            label: 'Title Color',
            default: '#ffffff',
            tab: 'style'
        },
        {
            key: 'titleShadow',
            type: 'boolean',
            label: 'Text Shadow',
            default: true,
            tab: 'style'
        },

        { type: 'header', label: 'Subtitle Style', tab: 'style' },
        {
            key: 'subtitleFontFamily',
            type: 'select',
            label: 'Font Family',
            options: fontFamilyOptions,
            default: 'inherit',
            tab: 'style'
        },
        {
            key: 'subtitleSize',
            type: 'slider',
            label: 'Font Size',
            min: 14,
            max: 48,
            step: 1,
            unit: 'px',
            default: 20,
            responsive: true,
            tab: 'style'
        },
        {
            key: 'subtitleWeight',
            type: 'toggle_group',
            label: 'Font Weight',
            options: fontWeightOptions.slice(0, 4), // Light to Semi
            default: '400',
            tab: 'style'
        },
        {
            key: 'subtitleColor',
            type: 'color',
            label: 'Subtitle Color',
            default: 'rgba(255, 255, 255, 0.8)',
            tab: 'style'
        },
        {
            key: 'subtitleMaxWidth',
            type: 'slider',
            label: 'Max Width',
            min: 300,
            max: 1200,
            step: 50,
            unit: 'px',
            default: 700,
            tab: 'style'
        },

        { type: 'header', label: 'Background', tab: 'style' },
        {
            key: 'bgType',
            type: 'toggle_group',
            label: 'Background Type',
            options: [
                { label: 'Color', value: 'color' },
                { label: 'Image', value: 'image' },
                { label: 'Gradient', value: 'gradient' }
            ],
            default: 'color',
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: '#18181b',
            tab: 'style'
        },
        {
            key: 'bgImage',
            type: 'image',
            label: 'Background Image',
            default: '',
            tab: 'style'
        },
        {
            key: 'bgSize',
            type: 'select',
            label: 'Background Size',
            options: bgSizeOptions,
            default: 'cover',
            tab: 'style'
        },
        {
            key: 'bgPosition',
            type: 'select',
            label: 'Background Position',
            options: bgPositionOptions,
            default: 'center',
            tab: 'style'
        },
        {
            key: 'gradientStart',
            type: 'color',
            label: 'Gradient Start',
            default: '#3b82f6',
            tab: 'style'
        },
        {
            key: 'gradientEnd',
            type: 'color',
            label: 'Gradient End',
            default: '#8b5cf6',
            tab: 'style'
        },
        {
            key: 'gradientDirection',
            type: 'select',
            label: 'Gradient Direction',
            options: gradientDirectionOptions,
            default: 'to bottom right',
            tab: 'style'
        },

        { type: 'header', label: 'Overlay', tab: 'style' },
        {
            key: 'overlayEnabled',
            type: 'boolean',
            label: 'Enable Overlay',
            default: true,
            tab: 'style'
        },
        {
            key: 'overlayColor',
            type: 'color',
            label: 'Overlay Color',
            default: 'rgba(0, 0, 0, 0.5)',
            tab: 'style'
        },
        {
            key: 'overlayOpacity',
            type: 'slider',
            label: 'Overlay Opacity',
            min: 0,
            max: 100,
            step: 5,
            default: 50,
            tab: 'style'
        },

        { type: 'header', label: 'Layout', tab: 'style' },
        {
            key: 'minHeight',
            type: 'slider',
            label: 'Min Height',
            min: 200,
            max: 1000,
            step: 50,
            unit: 'px',
            default: 500,
            responsive: true,
            tab: 'style'
        },
        {
            key: 'contentMaxWidth',
            type: 'slider',
            label: 'Content Max Width',
            min: 600,
            max: 1600,
            step: 100,
            unit: 'px',
            default: 1200,
            tab: 'style'
        },
        {
            key: 'verticalAlign',
            type: 'toggle_group',
            label: 'Vertical Align',
            options: [
                { label: 'Top', value: 'start' },
                { label: 'Center', value: 'center' },
                { label: 'Bottom', value: 'end' }
            ],
            default: 'center',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'box_model',
            mode: 'padding',
            label: 'Padding',
            default: { top: '80px', right: '24px', bottom: '80px', left: '24px' },
            tab: 'style'
        },
        {
            key: 'radius',
            type: 'toggle_group',
            label: 'Border Radius',
            options: borderRadiusPresets,
            default: 'rounded-none',
            tab: 'style'
        },

        // ============ ADVANCED TAB ============
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
                { label: 'Slide Up', value: 'animate-slide-up' }
            ],
            default: 'animate-fade',
            tab: 'advanced'
        },
        {
            key: 'animationDuration',
            type: 'slider',
            label: 'Duration (ms)',
            min: 200,
            max: 2000,
            step: 100,
            default: 700,
            tab: 'advanced'
        },
        {
            key: 'animationDelay',
            type: 'slider',
            label: 'Delay (ms)',
            min: 0,
            max: 1000,
            step: 100,
            default: 0,
            tab: 'advanced'
        },

        { type: 'header', label: 'Parallax', tab: 'advanced' },
        {
            key: 'parallaxEnabled',
            type: 'boolean',
            label: 'Enable Parallax',
            default: false,
            tab: 'advanced'
        },
        {
            key: 'parallaxSpeed',
            type: 'slider',
            label: 'Parallax Speed',
            min: 0.1,
            max: 1,
            step: 0.1,
            default: 0.5,
            tab: 'advanced'
        }
    ],

    defaultSettings: {
        // Content
        title: 'Build the Future of the Web\nwith Infinite Precision.',
        titleTag: 'h1',
        subtitle: 'The world\'s most flexible visual builder for creators who demand pixel-perfect execution and lightning-fast performance.',

        // Title Style
        titleFontFamily: 'inherit',
        titleSize: 72,
        titleWeight: '900',
        titleAlign: 'center',
        titleColor: '#ffffff',
        titleShadow: true,

        // Subtitle Style
        subtitleFontFamily: 'inherit',
        subtitleSize: 22,
        subtitleWeight: '400',
        subtitleColor: 'rgba(255, 255, 255, 0.9)',
        subtitleMaxWidth: 800,

        // Background
        bgType: 'gradient',
        bgColor: '#18181b',
        bgImage: '',
        bgSize: 'cover',
        bgPosition: 'center',
        gradientStart: '#4f46e5',
        gradientEnd: '#7c3aed',
        gradientDirection: 'to bottom right',

        // Overlay
        overlayEnabled: true,
        overlayColor: 'rgba(0, 0, 0, 0.4)',
        overlayOpacity: 40,

        // Layout
        minHeight: 700,
        contentMaxWidth: 1200,
        verticalAlign: 'center',
        padding: { top: '120px', right: '24px', bottom: '120px', left: '24px' },
        radius: 'rounded-none',

        // Animation
        entranceAnimation: 'animate-fade-up',
        animationDuration: 1000,
        animationDelay: 0,
        parallaxEnabled: true,
        parallaxSpeed: 0.5,

        // Nested blocks
        blocks: [],
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
