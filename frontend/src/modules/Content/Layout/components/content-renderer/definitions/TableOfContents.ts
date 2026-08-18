import type { BlockDefinition } from '@/types/builder';
import ListOrdered from 'lucide-vue-next/dist/esm/icons/list-ordered.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'tableofcontents',
    label: 'Table of Contents',
    icon: ListOrdered,
    description: 'Auto-generated table of contents from headers.',
    component: defineAsyncComponent(() => import('@/shared/blocks/TableOfContentsBlock.vue')),
    settings: [
        { key: 'depth', type: 'number', label: 'Max Depth', default: 3 }
    ],
    defaultSettings: {
        depth: 3,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
