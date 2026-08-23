import type { BlockDefinition } from '@/types/builder';
/**
 * Enhanced Text Block Definition
 * Rich text with comprehensive typography and styling
 */

import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import AlignJustify from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-center.js';
import { defineAsyncComponent } from 'vue';
import { fontFamilyOptions, fontWeightOptions } from './presets/typography';
import { borderRadiusPresets } from './presets/border';

export default {
    name: 'text',
    label: 'Text Block',
    icon: Type,
    description: 'Rich text content with title and body text.',
    category: 'basic',
    component: defineAsyncComponent(() => import('@/shared/blocks/TextBlock.vue')),

    settings: [
        // ============ CONTENT TAB ============
        { type: 'header', label: 'Title', tab: 'content' },
        {
            key: 'showTitle',
            type: 'boolean',
            label: 'Show Title',
            default: true,
            tab: 'content'
        },
        {
            key: 'title',
            type: 'text',
            label: 'Title Text',
            default: 'Section Title',
            tab: 'content'
        },
        {
            key: 'titleTag',
            type: 'select',
            label: 'Title Tag',
            options: [
                { label: 'H1', value: 'h1' },
                { label: 'H2', value: 'h2' },
                { label: 'H3', value: 'h3' },
                { label: 'H4', value: 'h4' },
                { label: 'Div', value: 'div' }
            ],
            default: 'h2',
            tab: 'content'
        },

        { type: 'header', label: 'Content', tab: 'content' },
        {
            key: 'content',
            type: 'richtext',
            label: 'Body Text',
            default: 'Design beautiful layouts with zero compromise on performance. JA-Builder gives you the power of professional tools directly in your browser.',
            tab: 'content'
        },

        // ============ STYLE TAB ============
        { type: 'header', label: 'Title Typography', tab: 'style' },
        {
            key: 'titleFontFamily',
            type: 'select',
            label: 'Font Family',
            options: fontFamilyOptions,
            default: 'inherit',
            tab: 'style'
        },
        {
            key: 'titleFontSize',
            type: 'slider',
            label: 'Font Size',
            min: 16,
            max: 72,
            step: 2,
            unit: 'px',
            default: 32,
            responsive: true,
            tab: 'style'
        },
        {
            key: 'titleFontWeight',
            type: 'toggle_group',
            label: 'Font Weight',
            options: fontWeightOptions,
            default: '700',
            tab: 'style'
        },
        {
            key: 'titleColor',
            type: 'color',
            label: 'Title Color',
            default: 'inherit',
            tab: 'style'
        },
        {
            key: 'titleMarginBottom',
            type: 'slider',
            label: 'Title Bottom Spacing',
            min: 0,
            max: 80,
            step: 4,
            unit: 'px',
            default: 16,
            tab: 'style'
        },

        { type: 'header', label: 'Body Typography', tab: 'style' },
        {
            key: 'bodyFontFamily',
            type: 'select',
            label: 'Font Family',
            options: fontFamilyOptions,
            default: 'inherit',
            tab: 'style'
        },
        {
            key: 'bodyFontSize',
            type: 'slider',
            label: 'Font Size',
            min: 12,
            max: 32,
            step: 1,
            unit: 'px',
            default: 16,
            responsive: true,
            tab: 'style'
        },
        {
            key: 'bodyFontWeight',
            type: 'toggle_group',
            label: 'Font Weight',
            options: fontWeightOptions.slice(0, 4), // Light to Semi
            default: '400',
            tab: 'style'
        },
        {
            key: 'bodyColor',
            type: 'color',
            label: 'Body Color',
            default: 'inherit',
            tab: 'style'
        },
        {
            key: 'bodyLineHeight',
            type: 'slider',
            label: 'Line Height',
            min: 1,
            max: 2.5,
            step: 0.1,
            default: 1.7,
            tab: 'style'
        },

        { type: 'header', label: 'Alignment', tab: 'style' },
        {
            key: 'alignment',
            type: 'toggle_group',
            label: 'Text Alignment',
            options: [
                { label: 'Left', value: 'left', icon: AlignLeft },
                { label: 'Center', value: 'center', icon: AlignCenter },
                { label: 'Right', value: 'right', icon: AlignRight },
                { label: 'Justify', value: 'justify', icon: AlignJustify }
            ],
            default: 'left',
            responsive: true,
            tab: 'style'
        },

        { type: 'header', label: 'Container', tab: 'style' },
        {
            key: 'maxWidth',
            type: 'slider',
            label: 'Max Width',
            min: 400,
            max: 1400,
            step: 100,
            unit: 'px',
            default: 900,
            responsive: true,
            tab: 'style'
        },
        {
            key: 'containerAlignment',
            type: 'toggle_group',
            label: 'Container Position',
            options: [
                { label: 'Left', value: 'start', icon: AlignLeft },
                { label: 'Center', value: 'center', icon: AlignCenter },
                { label: 'Right', value: 'end', icon: AlignRight }
            ],
            default: 'center',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'box_model',
            mode: 'padding',
            label: 'Padding',
            default: { top: '48px', right: '24px', bottom: '48px', left: '24px' },
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent',
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
        { type: 'header', label: 'Prose Styling', tab: 'advanced' },
        {
            key: 'isProse',
            type: 'boolean',
            label: 'Use Prose Styling',
            default: true,
            tab: 'advanced'
        },
        {
            key: 'proseSize',
            type: 'toggle_group',
            label: 'Prose Size',
            options: [
                { label: 'Sm', value: 'prose-sm' },
                { label: 'Base', value: 'prose-base' },
                { label: 'Lg', value: 'prose-lg' },
                { label: 'XL', value: 'prose-xl' }
            ],
            default: 'prose-base',
            tab: 'advanced'
        },

        { type: 'header', label: 'Drop Cap', tab: 'advanced' },
        {
            key: 'dropCap',
            type: 'boolean',
            label: 'Enable Drop Cap',
            default: false,
            tab: 'advanced'
        },
        {
            key: 'dropCapColor',
            type: 'color',
            label: 'Drop Cap Color',
            default: '#3b82f6',
            tab: 'advanced'
        },

        { type: 'header', label: 'Columns', tab: 'advanced' },
        {
            key: 'columns',
            type: 'toggle_group',
            label: 'Text Columns',
            options: [
                { label: '1', value: '1' },
                { label: '2', value: '2' },
                { label: '3', value: '3' }
            ],
            default: '1',
            responsive: true,
            tab: 'advanced'
        },
        {
            key: 'columnGap',
            type: 'slider',
            label: 'Column Gap',
            min: 16,
            max: 80,
            step: 8,
            unit: 'px',
            default: 32,
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
                { label: 'Slide Up', value: 'animate-slide-up' }
            ],
            default: '',
            tab: 'advanced'
        }
    ],

    defaultSettings: {
        // Content
        showTitle: true,
        title: 'Section Title',
        titleTag: 'h2',
        content: 'Design beautiful layouts with zero compromise on performance. JA-Builder gives you the power of professional tools directly in your browser.',

        // Title Style
        titleFontFamily: 'inherit',
        titleFontSize: 32,
        titleFontWeight: '700',
        titleColor: 'inherit',
        titleMarginBottom: 16,

        // Body Style
        bodyFontFamily: 'inherit',
        bodyFontSize: 16,
        bodyFontWeight: '400',
        bodyColor: 'inherit',
        bodyLineHeight: 1.7,

        // Alignment
        alignment: 'left',

        // Container
        maxWidth: 900,
        containerAlignment: 'center',
        padding: { top: '48px', right: '24px', bottom: '48px', left: '24px' },
        bgColor: 'transparent',
        radius: 'rounded-none',

        // Advanced
        isProse: true,
        proseSize: 'prose-base',
        dropCap: false,
        dropCapColor: '#3b82f6',
        columns: '1',
        columnGap: 32,
        entranceAnimation: '',

        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
