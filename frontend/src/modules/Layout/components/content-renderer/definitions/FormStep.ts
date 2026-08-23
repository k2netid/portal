import { defineAsyncComponent } from 'vue';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import type { BlockDefinition } from '@/types/builder';

export default {
    name: 'form_step',
    label: 'Form Step',
    icon: Layers,
    category: 'forms',
    component: defineAsyncComponent(() => import('@/shared/blocks/FormStepBlock.vue')),

    fields: [
        { key: 'title', type: 'text', label: 'Title', default: 'Step 1' },
        { key: 'description', type: 'text', label: 'Description', default: '' },
    ]
} as BlockDefinition;
