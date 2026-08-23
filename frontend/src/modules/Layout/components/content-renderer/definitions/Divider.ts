import type { BlockDefinition } from '@/types/builder';
import Minus from 'lucide-vue-next/dist/esm/icons/minus.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'divider',
    label: 'Divider',
    icon: Minus,
    description: 'Visual separator line with multiple styles.',
    component: defineAsyncComponent(() => import('@/shared/blocks/DividerBlock.vue')),
    settings: [
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Solid Line', value: 'line' },
                { label: 'Dashed', value: 'dashed' },
                { label: 'Dotted', value: 'dotted' },
                { label: 'Double', value: 'double' },
                { label: 'Gradient', value: 'gradient' },
                { label: 'Shadow', value: 'shadow' }
            ],
            default: 'line',
            tab: 'style'
        },
        {
            key: 'color',
            type: 'color',
            label: 'Line Color',
            default: 'hsl(var(--border))',
            tab: 'style'
        },
        {
            key: 'height',
            type: 'toggle_group',
            label: 'Thickness',
            options: [
                { label: 'Sm', value: 'small' },
                { label: 'Md', value: 'medium' },
                { label: 'Lg', value: 'large' }
            ],
            default: 'medium',
            tab: 'style'
        },
        {
            key: 'width',
            type: 'toggle_group',
            label: 'Width',
            options: [
                { label: 'Full', value: 'max-w-full' },
                { label: 'Lg', value: 'max-w-4xl' },
                { label: 'Md', value: 'max-w-2xl' },
                { label: 'Sm', value: 'max-w-xl' }
            ],
            default: 'max-w-full',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Vertical Space',
            options: [
                { label: 'None', value: 'py-0' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-6' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-6',
            tab: 'style'
        }
    ],
    defaultSettings: {
        style: 'line',
        color: 'hsl(var(--border))',
        height: 'medium',
        width: 'max-w-full',
        padding: 'py-6',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
