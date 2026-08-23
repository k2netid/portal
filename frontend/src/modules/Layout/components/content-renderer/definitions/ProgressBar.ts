import type { BlockDefinition } from '@/types/builder';
import BarChart3 from 'lucide-vue-next/dist/esm/icons/chart-bar-stacked.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'progress-bar',
    label: 'Progress Bar',
    icon: BarChart3,
    description: 'Visual progress indicator.',
    component: defineAsyncComponent(() => import('@/shared/blocks/ProgressBarBlock.vue')),
    settings: [
        {
            key: 'label',
            type: 'text',
            label: 'Label',
            default: 'Progress'
        },
        {
            key: 'value',
            type: 'slider',
            label: 'Value',
            min: 0,
            max: 100,
            step: 5,
            unit: '%',
            default: 75
        },
        {
            key: 'showValue',
            type: 'boolean',
            label: 'Show Percentage',
            default: true
        },
        {
            key: 'variant',
            type: 'select',
            label: 'Color',
            options: [
                { label: 'Primary', value: 'primary' },
                { label: 'Success', value: 'success' },
                { label: 'Warning', value: 'warning' },
                { label: 'Error', value: 'error' },
                { label: 'Info', value: 'info' }
            ],
            default: 'primary'
        },
        {
            key: 'barColor',
            type: 'color',
            label: 'Custom Bar Color',
            default: ''
        },
        {
            key: 'height',
            type: 'range',
            label: 'Bar Height',
            min: 8,
            max: 40,
            default: 20,
            tab: 'style'
        },
        {
            key: 'borderRadius',
            type: 'range',
            label: 'Bar Roundness',
            min: 0,
            max: 20,
            default: 10,
            tab: 'style'
        },
        {
            key: 'trackColor',
            type: 'color',
            label: 'Track Color',
            default: '#e0e0e0',
            tab: 'style'
        },
        {
            key: 'striped',
            type: 'boolean',
            label: 'Striped Pattern',
            default: false
        },
        {
            key: 'animated',
            type: 'boolean',
            label: 'Animated',
            default: false
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Border Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded-md' },
                { label: 'Full', value: 'rounded-full' }
            ],
            default: 'rounded-full'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-6' },
                { label: 'Large', value: 'py-8' }
            ],
            default: 'py-6'
        }
    ],
    defaultSettings: {
        label: 'Progress',
        value: 75,
        showValue: true,
        variant: 'primary',
        barColor: '',
        height: 'medium',
        striped: false,
        animated: false,
        radius: 'rounded-full',
        width: 'max-w-2xl',
        padding: 'py-6',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
