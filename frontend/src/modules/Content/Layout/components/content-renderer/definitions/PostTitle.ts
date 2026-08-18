import type { BlockDefinition } from '@/types/builder';
import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'posttitle',
    label: 'Post Title',
    icon: Type,
    description: 'Display dynamic post title with meta.',
    component: defineAsyncComponent(() => import('@/shared/blocks/PostTitleBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Draft Title (In Builder)', default: '' },
        {
            key: 'tag', type: 'select', label: 'Tag', options: [
                { label: 'H1', value: 'h1' },
                { label: 'H2', value: 'h2' },
                { label: 'H3', value: 'h3' }
            ], default: 'h1'
        },
        { key: 'show_date', type: 'boolean', label: 'Show Date', default: true },
        { key: 'show_author', type: 'boolean', label: 'Show Author', default: true },
        { key: 'show_category', type: 'boolean', label: 'Show Category', default: true }
    ],
    defaultSettings: {
        title: '',
        tag: 'h1',
        show_date: true,
        show_author: true,
        show_category: true,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
