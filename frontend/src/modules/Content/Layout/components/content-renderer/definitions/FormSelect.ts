import { defineAsyncComponent } from 'vue';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import type { BlockDefinition } from '@/types/builder';

export default {
    name: 'form_select',
    label: 'Select Field',
    icon: ChevronDown,
    category: 'forms',
    component: defineAsyncComponent(() => import('@/shared/blocks/FormSelectBlock.vue')),

    fields: [
        { key: 'label', type: 'text', label: 'Label', default: 'Subject' },
        { key: 'field_id', type: 'text', label: 'Field ID (Unique)', default: 'subject' },
        {
            key: 'options',
            type: 'repeater',
            label: 'Options',
            default: [
                { label: 'Option 1', value: 'opt1' },
                { label: 'Option 2', value: 'opt2' }
            ]
        },
        { key: 'is_required', type: 'switch', label: 'Required', default: false },
        { key: 'help_text', type: 'text', label: 'Help Text', default: '' },
    ]
} as BlockDefinition;
