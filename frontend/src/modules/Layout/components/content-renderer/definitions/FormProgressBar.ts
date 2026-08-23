import { defineAsyncComponent } from 'vue';
import MoreHorizontal from 'lucide-vue-next/dist/esm/icons/ellipsis.js';
import type { BlockDefinition } from '@/types/builder';

export default {
    name: 'form_progress',
    label: 'Progress Bar',
    icon: MoreHorizontal,
    category: 'forms',
    component: defineAsyncComponent(() => import('@/shared/blocks/FormProgressBarBlock.vue')),

    fields: [
        { key: 'show_percentage', type: 'switch', label: 'Show Percentage', default: true },
        { key: 'show_steps', type: 'switch', label: 'Show Steps', default: true },
    ]
} as BlockDefinition;
