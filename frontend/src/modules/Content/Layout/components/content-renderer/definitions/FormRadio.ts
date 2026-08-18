import { defineAsyncComponent } from 'vue';
import Disc from 'lucide-vue-next/dist/esm/icons/disc.js';
import type { BlockDefinition } from '@/types/builder';

export default {
    name: 'form_radio',
    label: 'Radio Field',
    icon: Disc,
    category: 'forms',
    component: defineAsyncComponent(() => import('@/shared/blocks/FormRadioBlock.vue')),

    fields: [
        { key: 'label', type: 'text', label: 'Label', default: 'Preference' },
        { key: 'field_id', type: 'text', label: 'Field ID (Unique)', default: 'preference' },
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
