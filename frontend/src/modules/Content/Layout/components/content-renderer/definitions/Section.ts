import type { BlockDefinition } from '@/types/builder';
/**
 * Enhanced Section Block Definition
 * Root level layout block with comprehensive background and spacing controls
 */

import LayoutPanelTop from 'lucide-vue-next/dist/esm/icons/layout-panel-top.js';
import { defineAsyncComponent } from 'vue';

// Import Presets
import { borderRadiusPresets } from './presets/border';
import { shadowOptions } from './presets/effects';

export default {
    name: 'section',
    label: 'Section',
    icon: LayoutPanelTop,
    category: 'structure',
    component: defineAsyncComponent(() => import('@/shared/blocks/SectionBlock.vue')),

    settings: [
        // ============ LAYOUT TAB ============
        { type: 'header', label: 'Layout', tab: 'style' },
        {
            key: 'full_width',
            type: 'switch',
            label: 'Full Width',
            default: false,
            tab: 'style'
        },
        {
            key: 'minHeight',
            type: 'slider',
            label: 'Min Height',
            min: 0,
            max: 1200,
            step: 50,
            unit: 'px',
            default: 100,
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
            default: 'start',
            tab: 'style'
        },

        { type: 'header', label: 'Spacing', tab: 'style' },
        {
            key: 'padding',
            type: 'box_model',
            mode: 'padding',
            label: 'Padding',
            default: { top: '64px', right: '0', bottom: '64px', left: '0' },
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
        // We'll reuse the backgroundDefaults but maybe simplified or expanded?
        // Let's use the full set manually to ensure it maps correctly to what we want
        { type: 'header', label: 'Background', tab: 'style' },
        {
            key: 'bgType',
            type: 'toggle_group',
            label: 'Type',
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
            label: 'Color',
            default: 'transparent',
            condition: (s: Record<string, unknown>) => s.bgType === 'color',
            tab: 'style'
        },
        {
            key: 'bgImage',
            type: 'image',
            label: 'Image',
            default: '',
            condition: (s: Record<string, unknown>) => s.bgType === 'image',
            tab: 'style'
        },
        {
            key: 'bgSize',
            type: 'select',
            label: 'Size',
            options: [
                { label: 'Cover', value: 'cover' },
                { label: 'Contain', value: 'contain' },
                { label: 'Auto', value: 'auto' }
            ],
            default: 'cover',
            condition: (s: Record<string, unknown>) => s.bgType === 'image',
            tab: 'style'
        },
        {
            key: 'bgPosition',
            type: 'select',
            label: 'Position',
            options: [
                { label: 'Center', value: 'center' },
                { label: 'Top', value: 'top' },
                { label: 'Bottom', value: 'bottom' }
            ],
            default: 'center',
            condition: (s: Record<string, unknown>) => s.bgType === 'image',
            tab: 'style'
        },
        // Gradient props
        {
            key: 'gradientStart',
            type: 'color',
            label: 'Gradient Start',
            default: '#3b82f6',
            condition: (s: Record<string, unknown>) => s.bgType === 'gradient',
            tab: 'style'
        },
        {
            key: 'gradientEnd',
            type: 'color',
            label: 'Gradient End',
            default: '#8b5cf6',
            condition: (s: Record<string, unknown>) => s.bgType === 'gradient',
            tab: 'style'
        },
        {
            key: 'gradientDirection',
            type: 'select',
            label: 'Direction',
            options: [
                { label: 'To Right', value: 'to right' },
                { label: 'To Bottom', value: 'to bottom' },
                { label: 'To Bottom Right', value: 'to bottom right' }
            ],
            default: 'to right',
            condition: (s: Record<string, unknown>) => s.bgType === 'gradient',
            tab: 'style'
        },

        { type: 'header', label: 'Border', tab: 'style' },
        {
            key: 'borderWidth',
            type: 'slider',
            label: 'Border Width',
            min: 0,
            max: 12,
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

        { type: 'header', label: 'Effects', tab: 'style' },
        {
            key: 'shadow',
            type: 'select',
            label: 'Shadow',
            options: shadowOptions,
            default: 'none',
            tab: 'style'
        },
        {
            key: 'overlayColor',
            type: 'color',
            label: 'Overlay',
            default: 'transparent',
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
        full_width: true,
        minHeight: 0,
        verticalAlign: 'start',
        padding: { top: '80px', right: '0', bottom: '80px', left: '0' },
        margin: { top: '0', right: '0', bottom: '0', left: '0' },

        bgType: 'color',
        bgColor: 'transparent',
        bgImage: '',
        bgSize: 'cover',
        bgPosition: 'center',

        borderWidth: 0,
        borderColor: '#e5e7eb',
        radius: 'rounded-none',
        shadow: 'none',
        overlayColor: 'transparent',

        blocks: [],
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
