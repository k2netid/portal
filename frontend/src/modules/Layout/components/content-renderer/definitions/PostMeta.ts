import type { BlockDefinition } from '@/types/builder';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'postmeta',
    label: 'Post Meta',
    icon: User,
    description: 'Displays post author, date, and categories.',
    component: defineAsyncComponent(() => import('@/shared/blocks/PostMetaBlock.vue')),
    settings: [
        { key: 'showAuthor', type: 'boolean', label: 'Show Author', default: true },
        { key: 'showDate', type: 'boolean', label: 'Show Date', default: true },
        { key: 'showCategories', type: 'boolean', label: 'Show Categories', default: true },
        { key: 'showReadingTime', type: 'boolean', label: 'Show Reading Time', default: false },
        { key: 'showComments', type: 'boolean', label: 'Show Comments Count', default: false },
        { key: 'separator', type: 'text', label: 'Separator', default: '•' },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ],
            default: 'left'
        }
    ],
    defaultSettings: {
        showAuthor: true,
        showDate: true,
        showCategories: true,
        showReadingTime: false,
        showComments: false,
        separator: '•',
        alignment: 'left',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
