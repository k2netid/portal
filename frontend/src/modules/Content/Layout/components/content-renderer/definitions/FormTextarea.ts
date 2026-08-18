import { defineAsyncComponent } from 'vue';
import TextAlignStart from 'lucide-vue-next/dist/esm/icons/text-align-start.js';
import type { BlockDefinition } from '@/types/builder';

export default {
    name: 'form_textarea',
    label: 'Textarea Field',
    icon: TextAlignStart,
    category: 'forms',
    component: defineAsyncComponent(() => import('@/shared/blocks/FormTextareaBlock.vue')),

    fields: [
        { key: 'label', type: 'text', label: 'Label', default: 'Message' },
        { key: 'field_id', type: 'text', label: 'Field ID (Unique)', default: 'message' },
        { key: 'rows', type: 'number', label: 'Rows', default: 4 },
        { key: 'placeholder', type: 'text', label: 'Placeholder', default: '' },
        { key: 'is_required', type: 'switch', label: 'Required', default: false },
        { key: 'help_text', type: 'text', label: 'Help Text', default: '' },
    ]
} as BlockDefinition;
