import type { BlockDefinition } from '@/types/builder';
import List from 'lucide-vue-next/dist/esm/icons/list.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'iconlist',
    label: 'Icon List',
    icon: List,
    description: 'Bulleted list with customizable icons.',
    component: defineAsyncComponent(() => import('@/shared/blocks/IconListBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Section Title',
            default: ''
        },
        {
            key: 'items',
            type: 'repeater',
            label: 'List Items',
            itemLabel: 'Item',
            fields: [
                { key: 'title', type: 'text', label: 'Title', default: 'List Item' },
                { key: 'description', type: 'textarea', label: 'Description', default: '' },
                {
                    key: 'icon',
                    type: 'select',
                    label: 'Icon',
                    options: [
                        { label: 'Check', value: 'check' },
                        { label: 'Check Circle', value: 'check-circle' },
                        { label: 'Star', value: 'star' },
                        { label: 'Arrow', value: 'arrow-right' },
                        { label: 'Zap', value: 'zap' },
                        { label: 'Shield', value: 'shield' },
                        { label: 'Heart', value: 'heart' },
                        { label: 'Sparkles', value: 'sparkles' }
                    ],
                    default: 'check'
                }
            ],
            default: [
                { title: 'First Item', description: 'Description for the first item.', icon: 'check' },
                { title: 'Second Item', description: 'Description for the second item.', icon: 'check' },
                { title: 'Third Item', description: 'Description for the third item.', icon: 'check' }
            ]
        },
        {
            key: 'defaultIcon',
            type: 'select',
            label: 'Default Icon',
            options: [
                { label: 'Check', value: 'check' },
                { label: 'Check Circle', value: 'check-circle' },
                { label: 'Star', value: 'star' },
                { label: 'Arrow', value: 'arrow-right' },
                { label: 'Zap', value: 'zap' },
                { label: 'Shield', value: 'shield' },
                { label: 'Heart', value: 'heart' },
                { label: 'Sparkles', value: 'sparkles' }
            ],
            default: 'check'
        },
        {
            key: 'iconSize',
            type: 'select',
            label: 'Icon Size',
            options: [
                { label: 'Small', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Large', value: 'large' }
            ],
            default: 'medium'
        },
        {
            key: 'iconColor',
            type: 'color',
            label: 'Icon Color',
            default: ''
        },
        {
            key: 'iconBgColor',
            type: 'color',
            label: 'Icon Background',
            default: ''
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ],
            default: 'left'
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
        title: 'Platform Capabilities',
        items: [
            { title: 'Real-time Collaboration', description: 'Work together with your team in a shared visual space.', icon: 'check-circle' },
            { title: 'Advanced Performance', description: 'Optimized for the modern web with zero-config edge delivery.', icon: 'check-circle' },
            { title: 'Universal Export', description: 'Clean, production-ready code available at the touch of a button.', icon: 'check-circle' }
        ],
        defaultIcon: 'check-circle',
        iconSize: 'medium',
        iconColor: '#4f46e5',
        iconBgColor: 'rgba(79, 70, 229, 0.05)',
        alignment: 'left',
        width: 'max-w-3xl',
        padding: 'py-20',
        bgColor: 'transparent',
        animation: 'animate-fade-up',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
