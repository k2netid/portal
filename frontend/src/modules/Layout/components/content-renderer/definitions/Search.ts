import type { BlockDefinition } from '@/types/builder';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'search',
    label: 'Search Bar',
    icon: Search,
    description: 'Allow users to search your content.',
    component: defineAsyncComponent(() => import('@/shared/blocks/SearchBlock.vue')),
    settings: [
        {
            key: 'placeholder',
            type: 'text',
            label: 'Placeholder Text',
            default: 'Search...',
            responsive: true
        },
        {
            key: 'button_text',
            type: 'text',
            label: 'Button text',
            default: 'Search',
            responsive: true
        },
        {
            key: 'show_button',
            type: 'boolean',
            label: 'Show Button',
            default: true
        },
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Standard', value: 'border-input bg-background' },
                { label: 'Modern (Floating)', value: 'border-none bg-background shadow-xl' },
                { label: 'Minimal', value: 'border-b bg-transparent rounded-none px-0' }
            ],
            default: 'border-input bg-background'
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Corner Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded-sm' },
                { label: 'Medium', value: 'rounded-md' },
                { label: 'Large', value: 'rounded-lg' },
                { label: 'Full', value: 'rounded-full' }
            ],
            default: 'rounded-md'
        }
    ],
    defaultSettings: {
        placeholder: 'Search...',
        button_text: 'Search',
        show_button: true,
        style: 'border-input bg-background',
        radius: 'rounded-md',
        padding: 'p-2',
        width: 'max-w-xl'
    }
} as BlockDefinition;
