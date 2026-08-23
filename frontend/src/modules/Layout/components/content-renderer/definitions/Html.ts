import type { BlockDefinition } from '@/types/builder';
import Code from 'lucide-vue-next/dist/esm/icons/code.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'html',
    label: 'Custom HTML',
    icon: Code,
    description: 'Embed raw HTML content.',
    category: 'advanced',
    component: defineAsyncComponent(() => import('@/shared/blocks/HtmlBlock.vue')),

    settings: [
        { type: 'header', label: 'HTML Content', tab: 'content' },
        {
            key: 'content',
            type: 'code',
            label: 'HTML Code',
            language: 'html',
            default: '<div class="p-4 bg-muted rounded">Custom HTML Block</div>',
            tab: 'content'
        },
        { type: 'header', label: 'Style', tab: 'style' },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: 'py-0' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-6' },
                { label: 'Large', value: 'py-10' }
            ],
            default: 'py-0',
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background',
            default: 'transparent',
            tab: 'style'
        }
    ],

    defaultSettings: {
        content: '<div class="p-4 bg-muted rounded">Custom HTML Block</div>',
        padding: 'py-0',
        bgColor: 'transparent',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
