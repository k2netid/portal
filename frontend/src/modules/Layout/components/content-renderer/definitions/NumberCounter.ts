import type { BlockDefinition } from '@/types/builder';
import Hash from 'lucide-vue-next/dist/esm/icons/hash.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'numbercounter',
    label: 'Number Counter',
    icon: Hash,
    description: 'Animated counter for statistics.',
    component: defineAsyncComponent(() => import('@/shared/blocks/NumberCounterBlock.vue')),
    settings: [
        { key: 'endNumber', type: 'number', label: 'Target Number', default: 100 },
        { key: 'duration', type: 'number', label: 'Duration (ms)', default: 2000 }
    ],
    defaultSettings: {
        endNumber: 100,
        duration: 2000,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
