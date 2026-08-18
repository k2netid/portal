import { defineAsyncComponent } from 'vue';
import FormInput from 'lucide-vue-next/dist/esm/icons/form.js';
import type { BlockDefinition } from '@/types/builder';

export default {
    name: 'form_picker',
    label: 'Form Picker',
    icon: FormInput,
    category: 'forms',
    component: defineAsyncComponent(() => import('@/shared/blocks/FormPickerBlock.vue')),

    fields: [
        { key: 'form_slug', type: 'select', label: 'Form', default: '' },
        { key: 'show_title', type: 'switch', label: 'Show Title', default: true },
        { key: 'show_description', type: 'switch', label: 'Show Description', default: true },
    ]
} as BlockDefinition;
