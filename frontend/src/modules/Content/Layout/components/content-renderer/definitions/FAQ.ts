import type { BlockDefinition } from '@/types/builder';
import HelpCircle from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'faq',
    label: 'FAQ / Accordion',
    icon: HelpCircle,
    description: 'List of questions and answers in list or accordion layout.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FAQBlock.vue')),
    settings: [
        { type: 'header', label: 'Content', tab: 'content' },
        {
            key: 'items',
            type: 'repeater',
            label: 'FAQ Items',
            fields: [
                { key: 'question', type: 'text', label: 'Question', default: 'What is your question?' },
                { key: 'answer', type: 'textarea', label: 'Answer', default: 'Here is the answer.' }
            ],
            default: [
                { question: 'What makes this builder different?', answer: 'Our builder focuses on premium aesthetics and lightning-fast performance, giving you the best of both worlds.' },
                { question: 'Is it fully responsive?', answer: 'Absolutely. Every module is built with a mobile-first approach and can be further customized for tablet and desktop.' },
                { question: 'Can I export my designs?', answer: 'Yes, our system allows for clean code export that matches your visual designs exactly.' }
            ],
            tab: 'content'
        },

        { type: 'header', label: 'Layout & Style', tab: 'style' },
        {
            key: 'layout',
            type: 'select',
            label: 'Layout',
            options: [
                { label: 'Accordion (Expandable)', value: 'accordion' },
                { label: 'List (Static)', value: 'list' }
            ],
            default: 'accordion',
            tab: 'style'
        },
        {
            key: 'variant',
            type: 'select',
            label: 'Style Variant',
            options: [
                { label: 'Simple (Dividers)', value: 'simple' },
                { label: 'Boxed (Cards)', value: 'boxed' },
                { label: 'Minimal (Clean)', value: 'minimal' }
            ],
            default: 'boxed',
            tab: 'style'
        },
        { key: 'allowMultiple', type: 'boolean', label: 'Allow Multiple Open', default: false, tab: 'style' },

        { type: 'header', label: 'Colors', tab: 'style' },
        { key: 'itemBorderColor', type: 'color', label: 'Border Color', default: 'transparent', tab: 'style' },
        { key: 'activeBgColor', type: 'color', label: 'Active Background', default: '#f8fafc', tab: 'style' },
        { key: 'iconColor', type: 'color', label: 'Icon Color', default: '#4f46e5', tab: 'style' }
    ],
    defaultSettings: {
        items: [
            { question: 'What makes this builder different?', answer: 'Our builder focuses on premium aesthetics and lightning-fast performance, giving you the best of both worlds.' },
            { question: 'Is it fully responsive?', answer: 'Absolutely. Every module is built with a mobile-first approach and can be further customized for tablet and desktop.' },
            { question: 'Can I export my designs?', answer: 'Yes, our system allows for clean code export that matches your visual designs exactly.' }
        ],
        layout: 'accordion',
        variant: 'boxed',
        allowMultiple: false,
        padding: 'py-20',
        activeBgColor: '#f8fafc',
        iconColor: '#4f46e5',
        animation: 'animate-fade-up',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
