import type { BlockDefinition } from '@/types/builder';
import ToggleRight from 'lucide-vue-next/dist/esm/icons/toggle-right.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'toggle',
    label: 'Toggle',
    icon: ToggleRight,
    description: 'Collapsible toggle section.',
    component: defineAsyncComponent(() => import('@/shared/blocks/ToggleBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Title', default: 'Toggle Title' },
        { key: 'content', type: 'textarea', label: 'Content', default: 'Toggle content goes here.' },
        { key: 'open_by_default', type: 'boolean', label: 'Open by Default', default: false },
        {
            key: 'icon_style',
            type: 'select',
            label: 'Icon Style',
            options: [
                { label: 'Chevron', value: 'chevron' },
                { label: 'Plus/Minus', value: 'plus' }
            ],
            default: 'chevron'
        },
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Bordered', value: 'bordered' },
                { label: 'Filled', value: 'filled' },
                { label: 'Minimal', value: 'minimal' }
            ],
            default: 'bordered'
        },
        { key: 'padding', type: 'select', label: 'Padding', options: [{ label: 'None', value: '' }, { label: 'Small', value: 'py-2' }, { label: 'Medium', value: 'py-4' }, { label: 'Large', value: 'py-8' }], default: 'py-4' },
        { key: 'headerBackgroundColor', type: 'color', label: 'Header Background' },
        { key: 'contentBackgroundColor', type: 'color', label: 'Content Background' }
    ],
    defaultSettings: {
        title: 'Toggle Title',
        content: 'Toggle content goes here. Click the header to expand or collapse.',
        open_by_default: false,
        icon_style: 'chevron',
        style: 'bordered',
        padding: 'py-4',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
