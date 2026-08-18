import type { BlockDefinition } from '@/types/builder';
import Star from 'lucide-vue-next/dist/esm/icons/star.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'feature',
    label: 'Feature',
    icon: Star,
    description: 'Highlight a specific feature or service.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FeatureBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Title', default: 'New Feature' },
        { key: 'description', type: 'textarea', label: 'Description', default: 'Feature description goes here.' },
        { key: 'icon', type: 'icon', label: 'Icon', default: 'Star' }
    ],
    defaultSettings: {
        title: 'New Feature',
        description: 'Feature description goes here.',
        icon: 'Star',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
