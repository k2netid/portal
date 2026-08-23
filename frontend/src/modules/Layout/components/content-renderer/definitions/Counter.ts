import type { BlockDefinition } from '@/types/builder';
import Hash from 'lucide-vue-next/dist/esm/icons/hash.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'counter',
    label: 'Counter',
    icon: Hash,
    description: 'Animated number counter for statistics.',
    component: defineAsyncComponent(() => import('@/shared/blocks/CounterBlock.vue')),
    settings: [
        {
            key: 'number',
            type: 'text',
            label: 'Number',
            default: '100'
        },
        {
            key: 'prefix',
            type: 'text',
            label: 'Prefix',
            default: ''
        },
        { key: 'suffix', type: 'text', label: 'Suffix', default: '' },
        { key: 'decimals', type: 'number', label: 'Decimals', default: 0 },
        { key: 'separator', type: 'boolean', label: 'Thousands Separator', default: true },
        {
            key: 'label',
            type: 'text',
            label: 'Label',
            default: 'Total Users'
        },
        {
            key: 'duration',
            type: 'select',
            label: 'Animation Duration',
            options: [
                { label: 'Fast (1s)', value: 1000 },
                { label: 'Normal (2s)', value: 2000 },
                { label: 'Slow (3s)', value: 3000 }
            ],
            default: 2000
        },
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
            key: 'numberColor',
            type: 'color',
            label: 'Number Color',
            default: ''
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-12' },
                { label: 'Large', value: 'py-16' }
            ],
            default: 'py-12'
        }
    ],
    defaultSettings: {
        number: '100',
        prefix: '',
        suffix: '+',
        label: 'Total Users',
        duration: 2000,
        alignment: 'center',
        numberColor: '',
        width: 'max-w-xl',
        padding: 'py-12',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
