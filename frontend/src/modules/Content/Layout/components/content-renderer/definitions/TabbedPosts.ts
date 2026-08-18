import type { BlockDefinition } from '@/types/builder';
import LayoutGrid from 'lucide-vue-next/dist/esm/icons/layout-grid.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'TabbedPosts',
    label: 'Tabbed Posts',
    category: 'Magazine',
    icon: LayoutGrid,
    component: defineAsyncComponent(() => import('@/shared/blocks/TabbedPostsBlock.vue')),
    settings: [
        { key: 'title', label: 'Section Title', type: 'text', default: 'Latest Updates' },
        {
            key: 'categories',
            label: 'Categories (Comma Separated)',
            type: 'text',
            default: 'Technology, Lifestyle, Design',
            description: 'Enter category names or IDs to create tabs.'
        },
        { key: 'limit', label: 'Posts Per Tab', type: 'number', default: 4 },
        {
            key: 'columns',
            label: 'Columns',
            type: 'select',
            options: [
                { label: '2 Columns', value: 2 },
                { label: '3 Columns', value: 3 },
                { label: '4 Columns', value: 4 }
            ],
            default: 3
        }
    ],
    defaultSettings: {
        title: 'Latest Updates',
        categories: 'Technology, Lifestyle, Design',
        limit: 4,
        columns: 3,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
