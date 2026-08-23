import type { BlockDefinition } from '@/types/builder';
import Code from 'lucide-vue-next/dist/esm/icons/code.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'embed',
    label: 'Embed',
    icon: Code,
    description: 'Embed external content like iframes or widgets.',
    component: defineAsyncComponent(() => import('@/shared/blocks/EmbedBlock.vue')),
    settings: [
        { key: 'embedCode', type: 'textarea', label: 'Embed Code (HTML)', default: '' }
    ],
    defaultSettings: {
        embedCode: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
