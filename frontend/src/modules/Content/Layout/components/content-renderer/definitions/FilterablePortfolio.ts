import type { BlockDefinition } from '@/types/builder';
import Filter from 'lucide-vue-next/dist/esm/icons/list-filter.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'filterableportfolio',
    label: 'Filterable Portfolio',
    icon: Filter,
    description: 'Portfolio with dynamic category filtering.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FilterablePortfolioBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Title', default: 'Our Works' },
        { key: 'categories', type: 'text', label: 'Categories (comma separated)', default: 'All, Design, Web' }
    ],
    defaultSettings: {
        title: 'Our Works',
        categories: 'All, Design, Web',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
