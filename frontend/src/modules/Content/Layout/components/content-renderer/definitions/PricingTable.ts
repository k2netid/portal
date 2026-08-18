import type { BlockDefinition } from '@/types/builder';
import CreditCard from 'lucide-vue-next/dist/esm/icons/credit-card.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'pricingtable',
    label: 'Pricing Table',
    icon: CreditCard,
    description: 'Compare plans and pricing.',
    component: defineAsyncComponent(() => import('@/shared/blocks/PricingBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Section Title',
            default: 'Simple Pricing',
            tab: 'content'
        },
        {
            key: 'items',
            type: 'repeater',
            label: 'Plans',
            itemLabel: 'Plan',
            fields: [
                { key: 'name', type: 'text', label: 'Plan Name', default: 'New Plan' },
                { key: 'isFeatured', type: 'boolean', label: 'Featured Plan', default: false },
                { key: 'price', type: 'text', label: 'Price', default: '$99' },
                { key: 'features', type: 'list', label: 'Features', default: ['Feature 1', 'Feature 2'] },
                { key: 'buttonText', type: 'text', label: 'Button Text', default: 'Sign Up' }
            ],
            default: [
                { name: 'Starter', price: '$29', features: ['All Core Features', '5 Projects', 'Community Support'], buttonText: 'Start Free' },
                { name: 'Pro', price: '$99', features: ['Everything in Starter', 'Unlimited Projects', 'Priority Support', 'Advanced Analytics'], buttonText: 'Go Pro' },
                { name: 'Enterprise', price: '$299', features: ['Custom Solutions', 'Dedicated Manager', 'SLA', 'SSO'], buttonText: 'Contact Us' }
            ],
            tab: 'content'
        },
        {
            key: 'columns',
            type: 'range',
            label: 'Columns',
            min: 1,
            max: 4,
            default: 3,
            tab: 'style'
        },
        {
            key: 'gap',
            type: 'range',
            label: 'Gap',
            min: 0,
            max: 80,
            default: 32,
            tab: 'style'
        },
        {
            key: 'accentColor',
            type: 'color',
            label: 'Accent Color',
            default: '#4f46e5',
            tab: 'style'
        },
        {
            key: 'cardBackgroundColor',
            type: 'color',
            label: 'Card Background',
            default: '#ffffff',
            tab: 'style'
        },
        {
            key: 'featuredCardBackgroundColor',
            type: 'color',
            label: 'Featured Card Background',
            default: '#ffffff',
            tab: 'style'
        },
        {
            key: 'width',
            type: 'select',
            label: 'Width',
            options: [
                { label: 'Medium', value: 'max-w-6xl' },
                { label: 'Large', value: 'max-w-7xl' }
            ],
            default: 'max-w-6xl',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Medium', value: 'py-20' },
                { label: 'Large', value: 'py-32' }
            ],
            default: 'py-20',
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent',
            tab: 'style'
        },
    ],
    defaultSettings: {
        title: 'Simple Pricing',
        items: [
            { name: 'Starter', price: '29', isFeatured: false, features: ['All Core Features', '5 Projects', 'Community Support'], buttonText: 'Start Free' },
            { name: 'Pro', price: '99', isFeatured: true, features: ['Everything in Starter', 'Unlimited Projects', 'Priority Support', 'Advanced Analytics'], buttonText: 'Go Pro' },
            { name: 'Enterprise', price: '299', isFeatured: false, features: ['Custom Solutions', 'Dedicated Manager', 'SLA', 'SSO'], buttonText: 'Contact Us' }
        ],
        width: 'max-w-6xl',
        padding: 'py-20',
        bgColor: 'transparent',
        radius: 'rounded-none',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
