import type { BlockDefinition } from '@/types/builder';
import Type from 'lucide-vue-next/dist/esm/icons/type.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'contact_field',
    label: 'Contact Field',
    icon: Type,
    description: 'A single input field for a contact form.',
    component: defineAsyncComponent(() => import('@/shared/blocks/ContactFieldBlock.vue')),
    settings: [
        { key: 'name', type: 'text', label: 'Field Name', default: 'field' },
        { key: 'type', type: 'select', label: 'Type', options: [{ label: 'Text', value: 'text' }, { label: 'Email', value: 'email' }, { label: 'Message', value: 'textarea' }], default: 'text' }
    ],
    defaultSettings: {
        name: 'field',
        type: 'text',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
