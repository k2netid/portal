import type { BlockDefinition } from '@/types/builder';
import Quote from 'lucide-vue-next/dist/esm/icons/quote.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'quote',
    label: 'Quote',
    icon: Quote,
    description: 'Styled blockquote with author.',
    component: defineAsyncComponent(() => import('@/shared/blocks/QuoteBlock.vue')),
    settings: [
        {
            key: 'quote',
            type: 'textarea',
            label: 'Quote Text',
            default: 'The only way to do great work is to love what you do.'
        },
        {
            key: 'author',
            type: 'text',
            label: 'Author Name',
            default: 'Steve Jobs'
        },
        {
            key: 'role',
            type: 'text',
            label: 'Author Role',
            default: 'Co-founder, Apple Inc.'
        },
        {
            key: 'authorImage',
            type: 'image',
            label: 'Author Photo',
            default: ''
        },
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Border', value: 'border' },
                { label: 'Card', value: 'card' },
                { label: 'Minimal', value: 'minimal' },
                { label: 'Centered', value: 'centered' }
            ],
            default: 'border'
        },
        {
            key: 'showQuoteIcon',
            type: 'boolean',
            label: 'Show Quote Icon',
            default: true
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-12' },
                { label: 'Large', value: 'py-16' }
            ],
            default: 'py-12'
        }
    ],
    defaultSettings: {
        quote: 'The only way to do great work is to love what you do.',
        author: 'Steve Jobs',
        role: 'Co-founder, Apple Inc.',
        authorImage: '',
        style: 'border',
        showQuoteIcon: true,
        width: 'max-w-3xl',
        padding: 'py-12',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
