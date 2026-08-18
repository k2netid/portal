import type { BlockDefinition } from '@/types/builder';
import Grid from 'lucide-vue-next/dist/esm/icons/grid-2x2.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'features',
    label: 'Feature Grid',
    icon: Grid,
    description: 'Display features in a responsive grid.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FeatureGridBlock.vue')),
    settings: [
        { type: 'header', label: 'Content', tab: 'content' },
        {
            key: 'title',
            type: 'text',
            label: 'Section Title',
            default: 'Core Features',
            tab: 'content'
        },
        {
            key: 'items',
            type: 'repeater',
            label: 'Features',
            itemLabel: 'Feature',
            fields: [
                { key: 'title', type: 'text', label: 'Title', default: 'New Feature' },
                {
                    key: 'icon',
                    type: 'icon',
                    label: 'Icon',
                    default: 'zap'
                },
                { key: 'description', type: 'textarea', label: 'Description', default: 'Feature description goes here.' }
            ],
            default: [
                { title: 'Visual Editor', description: 'Drag and drop elements with real-time feedback.' },
                { title: 'Responsive', description: 'Perfect look on every screen size.' },
                { title: 'Lightning Fast', description: 'Optimized for speed and SEO.' }
            ],
            tab: 'content'
        },
        { type: 'header', label: 'Layout', tab: 'style' },
        {
            key: 'columns',
            type: 'toggle_group',
            label: 'Columns',
            options: [
                { label: '2', value: '2' },
                { label: '3', value: '3' },
                { label: '4', value: '4' }
            ],
            default: '3',
            responsive: true,
            tab: 'style'
        },
        {
            key: 'gap',
            type: 'select',
            label: 'Gap',
            options: [
                { label: 'Small', value: 'gap-4' },
                { label: 'Medium', value: 'gap-8' },
                { label: 'Large', value: 'gap-12' }
            ],
            default: 'gap-8',
            tab: 'style'
        },
        {
            key: 'textAlign',
            type: 'toggle_group',
            label: 'Text Align',
            options: [
                { label: 'Left', value: 'text-left' },
                { label: 'Center', value: 'text-center' }
            ],
            default: 'text-left',
            tab: 'style'
        },

        { type: 'header', label: 'Card Style', tab: 'style' },
        {
            key: 'cardBgColor',
            type: 'color',
            label: 'Card Background',
            default: 'rgba(255, 255, 255, 0.5)', // slightly transparent white for glass effect
            tab: 'style'
        },
        {
            key: 'cardBorderColor',
            type: 'color',
            label: 'Card Border',
            default: 'rgba(0, 0, 0, 0.1)',
            tab: 'style'
        },
        {
            key: 'cardRadius',
            type: 'toggle_group',
            label: 'Card Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded-lg' },
                { label: 'Medium', value: 'rounded-2xl' },
                { label: 'Large', value: 'rounded-3xl' }
            ],
            default: 'rounded-2xl',
            tab: 'style'
        },
        {
            key: 'cardShadow',
            type: 'select',
            label: 'Card Shadow',
            options: [
                { label: 'None', value: 'shadow-none' },
                { label: 'Small', value: 'shadow-sm' },
                { label: 'Medium', value: 'shadow-md' },
                { label: 'Large', value: 'shadow-xl' }
            ],
            default: 'shadow-none',
            tab: 'style'
        },

        { type: 'header', label: 'Icon Style', tab: 'style' },
        {
            key: 'iconColor',
            type: 'color',
            label: 'Icon Color',
            default: '#2563eb', // primary blue
            tab: 'style'
        },
        {
            key: 'iconBgColor',
            type: 'color',
            label: 'Icon Background',
            default: 'rgba(37, 99, 235, 0.1)', // light blue
            tab: 'style'
        },
        {
            key: 'iconSize',
            type: 'select',
            label: 'Icon Size',
            options: [
                { label: 'Small', value: 'w-10 h-10' },
                { label: 'Medium', value: 'w-14 h-14' },
                { label: 'Large', value: 'w-20 h-20' }
            ],
            default: 'w-14 h-14',
            tab: 'style'
        },

        { type: 'header', label: 'Section', tab: 'style' },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
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
            label: 'Section Background',
            default: 'transparent',
            tab: 'style'
        },
        {
            key: 'animation',
            type: 'select',
            label: 'Animation',
            options: [
                { label: 'None', value: '' },
                { label: 'Fade In', value: 'animate-fade' },
                { label: 'Fade Up', value: 'animate-fade-up' }
            ],
            default: '',
            tab: 'style'
        }
    ],
    defaultSettings: {
        title: 'Core Features',
        items: [
            { title: 'Interactive Design', icon: 'zap', description: 'Create engaging experiences with our interactive module suite.' },
            { title: 'Global Scale', icon: 'globe', description: 'Deploy your content to edge locations everywhere for maximum speed.' },
            { title: 'Ironclad Security', icon: 'shield', description: 'Protected by enterprise-grade encryption and real-time monitoring.' }
        ],
        columns: '3',
        gap: 'gap-10',
        textAlign: 'text-left',
        cardBgColor: '#ffffff',
        cardBorderColor: 'transparent',
        cardRadius: 'rounded-3xl',
        cardShadow: 'shadow-xl',
        iconColor: '#2563eb',
        iconBgColor: 'rgba(37, 99, 235, 0.08)',
        iconSize: 'w-14 h-14',
        padding: 'py-24',
        bgColor: '#f8fafc',
        radius: 'rounded-none',
        animation: 'animate-fade-up',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
