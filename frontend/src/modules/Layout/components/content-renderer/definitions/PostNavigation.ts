import type { BlockDefinition } from '@/types/builder';
import ChevronLeft from 'lucide-vue-next/dist/esm/icons/chevron-left.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'postnav',
    label: 'Post Navigation',
    icon: ChevronLeft,
    description: 'Display links to next and previous posts.',
    component: defineAsyncComponent(() => import('@/shared/blocks/PostNavigationBlock.vue')),
    settings: [
        { key: 'showLabels', type: 'boolean', label: 'Show Labels', default: true },
        { key: 'prevLabel', type: 'text', label: 'Previous Label', default: 'Previous Post' },
        { key: 'nextLabel', type: 'text', label: 'Next Label', default: 'Next Post' },
        { key: 'label_color', type: 'color', label: 'Label & Icon Color', default: '#3b82f6' }
    ],
    defaultSettings: {
        showLabels: true,
        prevLabel: 'Previous Post',
        nextLabel: 'Next Post',
        label_color: '#3b82f6',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
