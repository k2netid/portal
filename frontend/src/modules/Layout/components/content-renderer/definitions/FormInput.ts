import { defineAsyncComponent } from 'vue';
import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import type { BlockDefinition } from '@/types/builder';

export default {
    name: 'form_input',
    label: 'Input Field',
    icon: Type,
    category: 'forms',
    component: defineAsyncComponent(() => import('@/shared/blocks/FormInputBlock.vue')),

    fields: [
        { key: 'label', type: 'text', label: 'Label', default: 'New Field' },
        { key: 'field_id', type: 'text', label: 'Field ID (Unique)', default: 'new_field' },
        {
            key: 'type',
            type: 'select',
            label: 'Type',
            options: [
                { label: 'Text', value: 'text' },
                { label: 'Email', value: 'email' },
                { label: 'Phone', value: 'tel' },
                { label: 'Number', value: 'number' },
            ],
            default: 'text'
        },
        { key: 'placeholder', type: 'text', label: 'Placeholder', default: '' },
        { key: 'is_required', type: 'switch', label: 'Required', default: false },
        { key: 'help_text', type: 'text', label: 'Help Text', default: '' },
    ]
} as BlockDefinition;
