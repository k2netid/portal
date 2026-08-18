import type { BlockDefinition } from '@/types/builder';
import Code from 'lucide-vue-next/dist/esm/icons/code.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidthcode',
    label: 'Fullwidth Code',
    icon: Code,
    description: 'Full-bleed code display with custom HTML/CSS/JS.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthCodeBlock.vue')),
    settings: [
        { key: 'rawContent', type: 'textarea', label: 'Raw Code Content', default: '' }
    ],
    defaultSettings: {
        rawContent: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
