import type { BlockDefinition } from '@/types/builder';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'postcontent',
    label: 'Post Content',
    icon: FileText,
    description: 'Display the main content of the post.',
    component: defineAsyncComponent(() => import('@/shared/blocks/PostContentBlock.vue')),
    settings: [
        { key: 'content', type: 'richtext', label: 'Draft Content (In Builder)', default: '' }
    ],
    defaultSettings: {
        content: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
