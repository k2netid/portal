import type { BlockDefinition } from '@/types/builder';
import Square from 'lucide-vue-next/dist/esm/icons/square.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidth_slide_item',
    label: 'Fullwidth Slide Item',
    icon: Square,
    description: 'A single slide in a fullwidth slider.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthSlideItemBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Title', default: 'Slide Title' },
        { key: 'subtitle', type: 'textarea', label: 'Subtitle', default: '' },
        { key: 'backgroundImage', type: 'image', label: 'Background Image' },
        { key: 'backgroundColor', type: 'color', label: 'Background Color', default: '#333333' },
        { key: 'buttonText', type: 'text', label: 'Button Text', default: '' },
        { key: 'buttonUrl', type: 'text', label: 'Button URL', default: '#' }
    ],
    defaultSettings: {
        title: 'Slide Title',
        subtitle: '',
        backgroundImage: '',
        backgroundColor: '#333333',
        buttonText: '',
        buttonUrl: '#',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
