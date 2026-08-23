import type { BlockDefinition } from '@/types/builder';
import Columns from 'lucide-vue-next/dist/esm/icons/columns-2.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'groupcarousel',
    label: 'Group Carousel',
    icon: Columns,
    description: 'Carousel of grouped content sections.',
    component: defineAsyncComponent(() => import('@/shared/blocks/GroupCarouselBlock.vue')),
    settings: [
        { key: 'autoplay', type: 'boolean', label: 'Autoplay', default: false }
    ],
    defaultSettings: {
        autoplay: false,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
