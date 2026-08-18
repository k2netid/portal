import { defineAsyncComponent } from 'vue';
import SquareCheck from 'lucide-vue-next/dist/esm/icons/square-check.js';
import type { BlockDefinition } from '@/types/builder';

export default {
    name: 'form_checkbox',
    label: 'Checkbox Field',
    icon: SquareCheck,
    category: 'forms',
    component: defineAsyncComponent(() => import('@/shared/blocks/FormCheckboxBlock.vue')),

    fields: [
        { key: 'label', type: 'text', label: 'Label', default: 'Interests' },
        { key: 'field_id', type: 'text', label: 'Field ID (Unique)', default: 'interests' },
        {
            key: 'options',
            type: 'repeater',
            label: 'Options',
            default: [
                { label: 'Option 1', value: 'opt1' }
            ]
        },
        { key: 'is_required', type: 'switch', label: 'Required', default: false },
        { key: 'help_text', type: 'text', label: 'Help Text', default: '' },
    ]
} as BlockDefinition;
