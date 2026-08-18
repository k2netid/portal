import type { BlockDefinition } from '@/types/builder';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'breadcrumbs',
    label: 'Breadcrumbs',
    icon: ChevronRight,
    description: 'Display navigation path to the current page.',
    component: defineAsyncComponent(() => import('@/shared/blocks/BreadcrumbsBlock.vue')),
    settings: [
        { key: 'items', type: 'repeater', label: 'Items', default: [{ text: 'Home', url: '/' }, { text: 'Current', url: '' }] },
        { key: 'separator', type: 'text', label: 'Separator', default: '/' },
        { key: 'showHome', type: 'boolean', label: 'Show Home Icon', default: true }
    ],
    defaultSettings: {
        items: [{ text: 'Home', url: '/' }, { text: 'Current', url: '' }],
        separator: '/',
        showHome: true,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
