import type { BlockDefinition } from '@/types/builder';
import LayoutGrid from 'lucide-vue-next/dist/esm/icons/layout-grid.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'portfolio',
    label: 'Filterable Portfolio',
    icon: LayoutGrid,
    description: 'Grid of portfolio items with category filters.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FilterablePortfolioBlock.vue')),
    settings: [
        { key: 'showFilter', type: 'boolean', label: 'Show Filter Tabs', default: true },
        { key: 'allLabel', type: 'text', label: 'All Items Label', default: 'All' },
        {
            key: 'filterAlignment',
            type: 'select',
            label: 'Filter Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ],
            default: 'center'
        },
        { key: 'columns', type: 'number', label: 'Columns', default: 4 },
        { key: 'postsCount', type: 'number', label: 'Posts Count', default: 12 },
        { key: 'showTitle', type: 'boolean', label: 'Show Project Title', default: true },
        { key: 'showCategories', type: 'boolean', label: 'Show Project Categories', default: true },
        { key: 'overlayColor', type: 'color', label: 'Hover Overlay Color', default: 'rgba(59, 130, 246, 0.9)' }
    ],
    defaultSettings: {
        showFilter: true,
        allLabel: 'All',
        filterAlignment: 'center',
        columns: 4,
        postsCount: 12,
        showTitle: true,
        showCategories: true,
        overlayColor: 'rgba(59, 130, 246, 0.9)',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
