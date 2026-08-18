import type { BlockDefinition } from '@/types/builder';
import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'richtext',
    label: 'Rich Text',
    icon: Type,
    description: 'Advanced text editor with full formatting support.',
    component: defineAsyncComponent(() => import('@/shared/blocks/RichTextBlock.vue')),
    settings: [
        {
            key: 'content',
            type: 'richtext',
            label: 'Content',
            default: '<p>Click to add text content...</p>',
            tab: 'content'
        }
    ],
    defaultSettings: {
        content: '<p>Click to add text content...</p>',
        alignment: 'left',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
