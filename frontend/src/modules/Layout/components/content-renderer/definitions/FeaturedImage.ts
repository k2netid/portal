import type { BlockDefinition } from '@/types/builder';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'postfeaturedimage',
    label: 'Post Featured Image',
    icon: Image,
    description: 'Display the featured image of the post.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FeaturedImageBlock.vue')),
    settings: [
        {
            key: 'aspectRatio',
            type: 'select',
            label: 'Aspect Ratio',
            options: [
                { label: '16:9', value: '16:9' },
                { label: '4:3', value: '4:3' },
                { label: '3:2', value: '3:2' },
                { label: '1:1', value: '1:1' },
                { label: 'Original', value: 'original' }
            ],
            default: '16:9'
        },
        { key: 'showCaption', type: 'boolean', label: 'Show Caption', default: true },
        { key: 'caption', type: 'text', label: 'Static Caption', default: '' }
    ],
    defaultSettings: {
        aspectRatio: '16:9',
        showCaption: true,
        caption: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
