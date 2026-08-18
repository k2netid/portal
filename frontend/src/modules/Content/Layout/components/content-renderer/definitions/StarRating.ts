import type { BlockDefinition } from '@/types/builder';
import Star from 'lucide-vue-next/dist/esm/icons/star.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'star_rating',
    label: 'Star Rating',
    icon: Star,
    description: 'Display star ratings for reviews and feedback.',
    component: defineAsyncComponent(() => import('@/shared/blocks/StarRatingBlock.vue')),
    settings: [
        { key: 'rating', type: 'slider', label: 'Rating', min: 0, max: 5, step: 0.5, default: 4 },
        { key: 'max', type: 'number', label: 'Maximum Stars', default: 5 },
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
        { key: 'color', type: 'color', label: 'Color', default: '' },
        { key: 'show_value', type: 'boolean', label: 'Show Value', default: true },
        { key: 'label', type: 'text', label: 'Label', default: '' },
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
                { label: 'Small', value: 'py-2' },
                { label: 'Medium', value: 'py-4' },
                { label: 'Large', value: 'py-8' }
            ],
            default: 'py-4'
        }
    ],
    defaultSettings: {
        rating: 4,
        max: 5,
        size: 'medium',
        color: '',
        show_value: true,
        label: '',
        alignment: 'center',
        padding: 'py-4',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
