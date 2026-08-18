import type { BlockDefinition } from '@/types/builder';
import PieChart from 'lucide-vue-next/dist/esm/icons/chart-pie.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'circle_counter',
    label: 'Circle Counter',
    icon: PieChart,
    description: 'Animated circular progress indicator.',
    component: defineAsyncComponent(() => import('@/shared/blocks/CircleCounterBlock.vue')),
    settings: [
        { key: 'value', type: 'slider', label: 'Value', min: 0, max: 100, step: 1, default: 75 },
        { key: 'max', type: 'number', label: 'Maximum', default: 100 },
        { key: 'title', type: 'text', label: 'Title (inside)', default: '' },
        { key: 'label', type: 'text', label: 'Label (below)', default: '' },
        {
            key: 'size',
            type: 'select',
            label: 'Size',
            options: [
                { label: 'Small', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Large', value: 'large' }
            ],
            default: 'medium'
        },
        { key: 'thickness', type: 'slider', label: 'Thickness', min: 4, max: 20, step: 2, default: 8 },
        { key: 'color', type: 'color', label: 'Color', default: '' },
        { key: 'show_value', type: 'boolean', label: 'Show Percentage', default: true },
        { key: 'animate', type: 'boolean', label: 'Animate on Load', default: true },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ],
            default: 'center'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: '' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-8' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-8'
        }
    ],
    defaultSettings: {
        value: 75,
        max: 100,
        title: '',
        label: '',
        size: 'medium',
        thickness: 8,
        color: '',
        show_value: true,
        animate: true,
        alignment: 'center',
        padding: 'py-8',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
