import type { BlockDefinition } from '@/types/builder';
import Box from 'lucide-vue-next/dist/esm/icons/box.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'numberbox',
    label: 'Number Box',
    icon: Box,
    description: 'Displays a large number with a label.',
    component: defineAsyncComponent(() => import('@/shared/blocks/NumberBoxBlock.vue')),
    settings: [
        { key: 'number', type: 'text', label: 'Number', default: '10' },
        { key: 'label', type: 'text', label: 'Label', default: 'Happy Clients' }
    ],
    defaultSettings: {
        number: '10',
        label: 'Happy Clients',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
