import type { BlockDefinition } from '@/types/builder';
import Share2 from 'lucide-vue-next/dist/esm/icons/share-2.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'sharebuttons',
    label: 'Share Buttons',
    icon: Share2,
    description: 'Social sharing buttons for the current page.',
    component: defineAsyncComponent(() => import('@/shared/blocks/ShareButtonsBlock.vue')),
    settings: [
        { key: 'networks', type: 'select', label: 'Networks', options: [{ label: 'All', value: 'all' }, { label: 'Selected', value: 'selected' }], default: 'all' }
    ],
    defaultSettings: {
        networks: 'all',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
