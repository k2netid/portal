import type { BlockDefinition } from '@/types/builder';
/**
 * Enhanced Container Block Definition
 * Flexible container with Flexbox controls, box styling, and layout options
 */

import Box from 'lucide-vue-next/dist/esm/icons/box.js';
import AlignHorizontalJustifyStart from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-start.js';
import AlignHorizontalJustifyCenter from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-center.js';
import AlignHorizontalJustifyEnd from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-end.js';
import AlignHorizontalSpaceBetween from 'lucide-vue-next/dist/esm/icons/align-horizontal-space-between.js';
import AlignVerticalJustifyStart from 'lucide-vue-next/dist/esm/icons/align-vertical-justify-start.js';
import AlignVerticalJustifyCenter from 'lucide-vue-next/dist/esm/icons/align-vertical-justify-center.js';
import AlignVerticalJustifyEnd from 'lucide-vue-next/dist/esm/icons/align-vertical-justify-end.js';
import { defineAsyncComponent } from 'vue';

// Import Presets
import { borderRadiusPresets } from './presets/border';
import { shadowOptions } from './presets/effects';

export default {
    name: 'container',
    label: 'Container',
    icon: Box,
    description: 'A flexible container for grouping and laying out other blocks.',
    category: 'structure',
    component: defineAsyncComponent(() => import('@/shared/blocks/ContainerBlock.vue')),

    settings: [
        // ============ LAYOUT TAB ============
        { type: 'header', label: 'Smart Layout', tab: 'style' },
        {
            key: 'layout',
            type: 'toggle_group',
            label: 'Layout Mode',
            options: [
                { label: 'Stack', value: 'stack', icon: AlignVerticalJustifyStart },
                { label: 'Row', value: 'row', icon: AlignHorizontalJustifyStart },
                // { label: 'Grid', value: 'grid', icon: LayoutGrid } // Future
            ],
            default: 'stack',
            responsive: true,
            tab: 'style'
        },
        { type: 'header', label: 'Flex Controls', tab: 'style' },
        {
            key: 'direction',
            type: 'toggle_group',
            label: 'Direction',
            options: [
                { label: 'Column', value: 'flex-col' },
                { label: 'Row', value: 'flex-row' },
                { label: 'Col Reverse', value: 'flex-col-reverse' },
                { label: 'Row Reverse', value: 'flex-row-reverse' }
            ],
            default: 'flex-col',
            condition: (settings: Record<string, unknown>) => !settings.layout || settings.layout === 'stack' || settings.layout === 'custom',
            tab: 'style'
        },
        {
            key: 'justify',
            type: 'toggle_group',
            label: 'Justify Content',
            options: [
                { label: 'Start', value: 'justify-start', icon: AlignHorizontalJustifyStart },
                { label: 'Center', value: 'justify-center', icon: AlignHorizontalJustifyCenter },
                { label: 'End', value: 'justify-end', icon: AlignHorizontalJustifyEnd },
                { label: 'Between', value: 'justify-between', icon: AlignHorizontalSpaceBetween }
            ],
            default: 'justify-start',
            tab: 'style'
        },
        {
            key: 'align',
            type: 'toggle_group',
            label: 'Align Items',
            options: [
                { label: 'Start', value: 'items-start', icon: AlignVerticalJustifyStart },
                { label: 'Center', value: 'items-center', icon: AlignVerticalJustifyCenter },
                { label: 'End', value: 'items-end', icon: AlignVerticalJustifyEnd },
                { label: 'Stretch', value: 'items-stretch' }
            ],
            default: 'items-start',
            tab: 'style'
        },
        {
            key: 'wrap',
            type: 'toggle_group',
            label: 'Wrap',
            options: [
                { label: 'No Wrap', value: 'flex-nowrap' },
                { label: 'Wrap', value: 'flex-wrap' },
                { label: 'Wrap Reverse', value: 'flex-wrap-reverse' }
            ],
            default: 'flex-wrap', // Smart Default
            tab: 'style'
        },
        {
            key: 'gap',
            type: 'select',
            label: 'Gap',
            options: [
                { label: '0px', value: '0px' },
                { label: '4px', value: '4px' },
                { label: '8px', value: '8px' },
                { label: '12px', value: '12px' },
                { label: '16px', value: '16px' },
                { label: '24px', value: '24px' },
                { label: '32px', value: '32px' },
                { label: '48px', value: '48px' },
                { label: '64px', value: '64px' }
            ],
            default: '16px',
            tab: 'style'
        },

        { type: 'header', label: 'Dimensions', tab: 'style' },
        {
            key: 'width',
            type: 'text',
            label: 'Width',
            placeholder: '100%, 300px, auto',
            default: '100%',
            tab: 'style'
        },
        {
            key: 'maxWidth',
            type: 'text',
            label: 'Max Width',
            placeholder: 'none, 100%, 800px',
            default: 'none',
            tab: 'style'
        },
        {
            key: 'minHeight',
            type: 'text',
            label: 'Min Height',
            placeholder: '0, 100px, 50vh',
            default: '0',
            tab: 'style'
        },

        { type: 'header', label: 'Spacing', tab: 'style' },
        {
            key: 'padding',
            type: 'box_model',
            mode: 'padding',
            label: 'Padding',
            default: { top: '16px', right: '16px', bottom: '16px', left: '16px' },
            tab: 'style'
        },
        {
            key: 'margin',
            type: 'box_model',
            mode: 'margin',
            label: 'Margin',
            default: { top: '0', right: '0', bottom: '0', left: '0' },
            tab: 'style'
        },

        // ============ BACKGROUND & BORDER ============
        { type: 'header', label: 'Style', tab: 'style' },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background',
            default: 'transparent',
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
            key: 'radius',
            type: 'toggle_group',
            label: 'Radius',
            options: borderRadiusPresets,
            default: 'rounded-none',
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

        // Overflow
        {
            key: 'overflow',
            type: 'select',
            label: 'Overflow',
            options: [
                { label: 'Visible', value: 'visible' },
                { label: 'Hidden', value: 'hidden' },
                { label: 'Scroll', value: 'scroll' },
                { label: 'Auto', value: 'auto' }
            ],
            default: 'visible',
            tab: 'style'
        },

        // ============ HIDDEN ============
        {
            key: 'blocks',
            type: 'hidden',
            default: []
        }
    ],

    defaultSettings: {
        layout: 'stack', // Smart Default
        direction: 'flex-col',
        justify: 'justify-start',
        align: 'items-start',
        wrap: 'flex-wrap', // Smart Default
        gap: '16px',
        width: '100%',
        maxWidth: 'none',
        minHeight: '0',
        padding: { top: '16px', right: '16px', bottom: '16px', left: '16px' },
        margin: { top: '0', right: '0', bottom: '0', left: '0' },
        bgColor: 'transparent',
        borderWidth: 0,
        borderColor: '#e5e7eb',
        radius: 'rounded-none',
        shadow: 'none',
        overflow: 'visible',
        blocks: [],
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
