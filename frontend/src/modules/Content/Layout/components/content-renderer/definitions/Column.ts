import type { BlockDefinition } from '@/types/builder';
/**
 * Column Block Definition
 * Individual column with flex layout and box styling controls
 * Used as child of ColumnsBlock
 */

import Columns3 from 'lucide-vue-next/dist/esm/icons/columns-3.js';
import AlignHorizontalJustifyStart from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-start.js';
import AlignHorizontalJustifyCenter from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-center.js';
import AlignHorizontalJustifyEnd from 'lucide-vue-next/dist/esm/icons/align-horizontal-justify-end.js';
import AlignVerticalJustifyStart from 'lucide-vue-next/dist/esm/icons/align-vertical-justify-start.js';
import AlignVerticalJustifyCenter from 'lucide-vue-next/dist/esm/icons/align-vertical-justify-center.js';
import AlignVerticalJustifyEnd from 'lucide-vue-next/dist/esm/icons/align-vertical-justify-end.js';
import { defineAsyncComponent } from 'vue';

// Import Presets
import { borderRadiusPresets } from './presets/border';

export default {
    name: 'column',
    label: 'Column',
    icon: Columns3,
    description: 'Individual column with layout and styling controls.',
    category: 'structure',
    hidden: true, // Not directly addable from sidebar, only via ColumnsBlock
    component: defineAsyncComponent(() => import('@/shared/blocks/ColumnBlock.vue')),

    settings: [
        // ============ LAYOUT ============
        { type: 'header', label: 'Layout', tab: 'style' },
        {
            key: 'direction',
            type: 'toggle_group',
            label: 'Direction',
            options: [
                { label: 'Column', value: 'flex-col' },
                { label: 'Row', value: 'flex-row' }
            ],
            default: 'flex-col',
            tab: 'style'
        },
        {
            key: 'justify',
            type: 'toggle_group',
            label: 'Justify',
            options: [
                { label: 'Start', value: 'justify-start', icon: AlignHorizontalJustifyStart },
                { label: 'Center', value: 'justify-center', icon: AlignHorizontalJustifyCenter },
                { label: 'End', value: 'justify-end', icon: AlignHorizontalJustifyEnd }
            ],
            default: 'justify-start',
            tab: 'style'
        },
        {
            key: 'align',
            type: 'toggle_group',
            label: 'Align',
            options: [
                { label: 'Start', value: 'items-start', icon: AlignVerticalJustifyStart },
                { label: 'Center', value: 'items-center', icon: AlignVerticalJustifyCenter },
                { label: 'End', value: 'items-end', icon: AlignVerticalJustifyEnd },
                { label: 'Stretch', value: 'items-stretch' }
            ],
            default: 'items-stretch',
            tab: 'style'
        },
        {
            key: 'gap',
            type: 'select',
            label: 'Gap',
            options: [
                { label: 'None', value: '0' },
                { label: 'Small (8px)', value: '2' },
                { label: 'Medium (16px)', value: '4' },
                { label: 'Large (32px)', value: '8' }
            ],
            default: '4',
            tab: 'style'
        },

        // ============ SPACING ============
        { type: 'header', label: 'Spacing', tab: 'style' },
        {
            key: 'padding',
            type: 'box_model',
            mode: 'padding',
            label: 'Padding',
            default: { top: '0', right: '0', bottom: '0', left: '0' },
            tab: 'style'
        },

        // ============ STYLE ============
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
            max: 8,
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

        // ============ HIDDEN ============
        {
            key: 'blocks',
            type: 'hidden',
            default: []
        }
    ],

    defaultSettings: {
        direction: 'flex-col',
        justify: 'justify-start',
        align: 'items-stretch',
        gap: '4',
        padding: { top: '0', right: '0', bottom: '0', left: '0' },
        bgColor: 'transparent',
        borderWidth: 0,
        borderColor: '#e5e7eb',
        radius: 'rounded-none',
        blocks: [],
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
