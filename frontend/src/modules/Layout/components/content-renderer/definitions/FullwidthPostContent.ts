import type { BlockDefinition } from '@/types/builder';
import FileText from 'lucide-vue-next/dist/esm/icons/file-text.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidthpostcontent',
    label: 'Fullwidth Post Content',
    icon: FileText,
    description: 'Display post content in a full-width container.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthPostContentBlock.vue')),
    settings: [
        {
            key: 'contentWidth', type: 'select', label: 'Width', options: [
                { label: 'Full Width', value: 'full' },
                { label: 'Boxed', value: 'boxed' }
            ], default: 'boxed'
        },
        { key: 'maxWidth', type: 'number', label: 'Max Width (px)', default: 1200 },
        { key: 'paragraphSpacing', type: 'number', label: 'Paragraph Spacing (px)', default: 24 },
        { key: 'headingSpacing', type: 'number', label: 'Heading Spacing (px)', default: 32 }
    ],
    defaultSettings: {
        contentWidth: 'boxed',
        maxWidth: 1200,
        paragraphSpacing: 24,
        headingSpacing: 32,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
