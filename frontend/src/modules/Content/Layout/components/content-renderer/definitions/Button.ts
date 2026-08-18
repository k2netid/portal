import type { BlockDefinition } from '@/types/builder';
/**
 * Enhanced Button Block Definition
 * Customizable CTA button with comprehensive styling options
 */

import MousePointer from 'lucide-vue-next/dist/esm/icons/mouse-pointer.js';
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import ArrowLeft from 'lucide-vue-next/dist/esm/icons/arrow-left.js';
import ArrowRight from 'lucide-vue-next/dist/esm/icons/arrow-right.js';
import { defineAsyncComponent } from 'vue';
import { fontFamilyOptions, fontWeightOptions } from './presets/typography';
import { borderRadiusPresets } from './presets/border';
import { shadowOptions } from './presets/effects';

// Extended icon options
const iconOptions = [
    { label: 'None', value: '' },
    { label: 'Arrow Right', value: 'arrow-right' },
    { label: 'Arrow Up Right', value: 'arrow-up-right' },
    { label: 'Chevron Right', value: 'chevron-right' },
    { label: 'External Link', value: 'external-link' },
    { label: 'Download', value: 'download' },
    { label: 'Play', value: 'play' },
    { label: 'Play Circle', value: 'play-circle' },
    { label: 'Mail', value: 'mail' },
    { label: 'Phone', value: 'phone' },
    { label: 'Send', value: 'send' },
    { label: 'Check', value: 'check' },
    { label: 'Plus', value: 'plus' },
    { label: 'Heart', value: 'heart' },
    { label: 'Star', value: 'star' },
    { label: 'Shopping Cart', value: 'shopping-cart' },
    { label: 'User', value: 'user' },
    { label: 'Sparkles', value: 'sparkles' }
];

export default {
    name: 'button',
    label: 'Button',
    icon: MousePointer,
    description: 'Customizable call-to-action button with icons and effects.',
    category: 'basic',
    component: defineAsyncComponent(() => import('@/shared/blocks/ButtonBlock.vue')),

    settings: [
        // ============ CONTENT TAB ============
        { type: 'header', label: 'Button Content', tab: 'content' },
        {
            key: 'text',
            type: 'text',
            label: 'Button Text',
            default: 'Click Here',
            tab: 'content'
        },
        {
            key: 'url',
            type: 'text',
            label: 'Link URL',
            default: '#',
            placeholder: 'https:// or #anchor',
            tab: 'content'
        },
        {
            key: 'openNewTab',
            type: 'boolean',
            label: 'Open in New Tab',
            default: false,
            tab: 'content'
        },

        { type: 'header', label: 'Icon', tab: 'content' },
        {
            key: 'iconName',
            type: 'select',
            label: 'Icon',
            options: iconOptions,
            default: '',
            tab: 'content'
        },
        {
            key: 'iconPosition',
            type: 'toggle_group',
            label: 'Icon Position',
            options: [
                { label: 'Left', value: 'left', icon: ArrowLeft },
                { label: 'Right', value: 'right', icon: ArrowRight }
            ],
            default: 'right',
            tab: 'content'
        },
        {
            key: 'iconSize',
            type: 'slider',
            label: 'Icon Size',
            min: 12,
            max: 32,
            step: 2,
            unit: 'px',
            default: 16,
            tab: 'content'
        },

        // ============ STYLE TAB ============
        { type: 'header', label: 'Button Style', tab: 'style' },
        {
            key: 'variant',
            type: 'toggle_group',
            label: 'Variant',
            options: [
                { label: 'Solid', value: 'primary' },
                { label: 'Soft', value: 'secondary' },
                { label: 'Outline', value: 'outline' },
                { label: 'Ghost', value: 'ghost' },
                { label: 'Link', value: 'link' }
            ],
            default: 'primary',
            tab: 'style'
        },
        {
            key: 'size',
            type: 'toggle_group',
            label: 'Size',
            options: [
                { label: 'XS', value: 'xs' },
                { label: 'Sm', value: 'small' },
                { label: 'Md', value: 'medium' },
                { label: 'Lg', value: 'large' },
                { label: 'XL', value: 'xl' }
            ],
            default: 'medium',
            tab: 'style'
        },
        {
            key: 'fullWidth',
            type: 'boolean',
            label: 'Full Width',
            default: false,
            tab: 'style'
        },

        { type: 'header', label: 'Custom Colors', tab: 'style' },
        {
            key: 'useCustomColors',
            type: 'boolean',
            label: 'Use Custom Colors',
            default: false,
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: '#3b82f6',
            tab: 'style'
        },
        {
            key: 'textColor',
            type: 'color',
            label: 'Text Color',
            default: '#ffffff',
            tab: 'style'
        },
        {
            key: 'borderColor',
            type: 'color',
            label: 'Border Color',
            default: '#3b82f6',
            tab: 'style'
        },

        { type: 'header', label: 'Typography', tab: 'style' },
        {
            key: 'fontFamily',
            type: 'select',
            label: 'Font Family',
            options: fontFamilyOptions,
            default: 'inherit',
            tab: 'style'
        },
        {
            key: 'fontWeight',
            type: 'toggle_group',
            label: 'Font Weight',
            options: fontWeightOptions.slice(2, 5), // Medium to Bold
            default: '600',
            tab: 'style'
        },
        {
            key: 'textTransform',
            type: 'select',
            label: 'Text Transform',
            options: [
                { label: 'None', value: 'none' },
                { label: 'Uppercase', value: 'uppercase' },
                { label: 'Capitalize', value: 'capitalize' }
            ],
            default: 'none',
            tab: 'style'
        },

        { type: 'header', label: 'Shape & Effects', tab: 'style' },
        {
            key: 'radius',
            type: 'toggle_group',
            label: 'Border Radius',
            options: borderRadiusPresets,
            default: 'rounded-lg',
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
            key: 'alignment',
            type: 'toggle_group',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left', icon: AlignLeft },
                { label: 'Center', value: 'center', icon: AlignCenter },
                { label: 'Right', value: 'right', icon: AlignRight }
            ],
            default: 'left',
            responsive: true,
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'box_model',
            mode: 'padding',
            label: 'Container Padding',
            default: { top: '16px', right: '0', bottom: '16px', left: '0' },
            tab: 'style'
        },

        // ============ ADVANCED TAB ============
        { type: 'header', label: 'Hover Effects', tab: 'advanced' },
        {
            key: 'hoverEffect',
            type: 'select',
            label: 'Hover Effect',
            options: [
                { label: 'None', value: 'none' },
                { label: 'Lift', value: 'lift' },
                { label: 'Glow', value: 'glow' },
                { label: 'Scale', value: 'scale' },
                { label: 'Shine', value: 'shine' }
            ],
            default: 'none',
            tab: 'advanced'
        },
        {
            key: 'hoverBgColor',
            type: 'color',
            label: 'Hover Background',
            default: '',
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
                { label: 'Bounce In', value: 'animate-bounce-in' }
            ],
            default: '',
            tab: 'advanced'
        },
        {
            key: 'animationDelay',
            type: 'slider',
            label: 'Animation Delay',
            min: 0,
            max: 1000,
            step: 100,
            unit: 'ms',
            default: 0,
            tab: 'advanced'
        }
    ],

    defaultSettings: {
        // Content
        text: 'Click Here',
        url: '#',
        openNewTab: false,
        iconName: '',
        iconPosition: 'right',
        iconSize: 16,

        // Style
        variant: 'primary',
        size: 'medium',
        fullWidth: false,
        useCustomColors: false,
        bgColor: '#3b82f6',
        textColor: '#ffffff',
        borderColor: '#3b82f6',
        fontFamily: 'inherit',
        fontWeight: '600',
        textTransform: 'none',
        radius: 'rounded-lg',
        shadow: 'none',
        alignment: 'left',
        padding: { top: '16px', right: '0', bottom: '16px', left: '0' },

        // Advanced
        hoverEffect: 'none',
        hoverBgColor: '',
        entranceAnimation: '',
        animationDelay: 0,

        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
