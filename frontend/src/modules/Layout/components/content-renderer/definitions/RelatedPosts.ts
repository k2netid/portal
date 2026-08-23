import type { BlockDefinition } from '@/types/builder';
import { defineAsyncComponent } from 'vue';
import GalleryHorizontal from 'lucide-vue-next/dist/esm/icons/gallery-horizontal.js';

export default {
    name: 'PostCarousel',
    label: 'Post Carousel',
    category: 'Magazine',
    icon: GalleryHorizontal,
    component: defineAsyncComponent(() => import('@/shared/blocks/PostSliderBlock.vue')),
    settings: [
        { name: 'title', label: 'Title', type: 'text', default: 'Trending Now' },
        { name: 'category', label: 'Category Filter', type: 'text', placeholder: 'tech, news' },
        { name: 'limit', label: 'Post Limit', type: 'number', default: 8 },
        {
            name: 'padding',
            label: 'Padding',
            type: 'select',
            options: [
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-12' },
                { label: 'Large', value: 'py-20' }
            ],
            default: 'py-12'
        },
        { name: 'show_excerpt', label: 'Show Excerpt', type: 'boolean', default: true },
        { name: 'show_date', label: 'Show Date', type: 'boolean', default: true }
    ]
} as BlockDefinition;
