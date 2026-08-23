import type { BlockDefinition } from '@/types/builder';
import LayoutList from 'lucide-vue-next/dist/esm/icons/layout-list.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'tabs',
    label: 'Tabs',
    icon: LayoutList,
    description: 'Tabbed content sections.',
    component: defineAsyncComponent(() => import('@/shared/blocks/TabsBlock.vue')),
    settings: [
        {
            key: 'tabs',
            type: 'repeater',
            label: 'Tabs',
            itemLabel: 'Tab',
            fields: [
                { key: 'title', type: 'text', label: 'Title', default: 'Tab Title' },
                { key: 'content', type: 'textarea', label: 'Content', default: '<p>Tab content...</p>' },
                {
                    key: 'icon',
                    type: 'select',
                    label: 'Icon',
                    options: [
                        { label: 'None', value: '' },
                        { label: 'Home', value: 'home' },
                        { label: 'User', value: 'user' },
                        { label: 'Settings', value: 'settings' },
                        { label: 'Star', value: 'star' },
                        { label: 'Heart', value: 'heart' },
                        { label: 'Zap', value: 'zap' }
                    ],
                    default: ''
                }
            ],
            default: [
                { id: 'tab-1', title: 'Overview', content: '<p>Overview content goes here...</p>', icon: '' },
                { id: 'tab-2', title: 'Features', content: '<p>Features content goes here...</p>', icon: '' },
                { id: 'tab-3', title: 'Pricing', content: '<p>Pricing content goes here...</p>', icon: '' }
            ]
        },
        {
            key: 'orientation',
            type: 'select',
            label: 'Layout',
            options: [
                { label: 'Horizontal (Top)', value: 'horizontal' },
                { label: 'Vertical (Left)', value: 'vertical' }
            ],
            default: 'horizontal',
            tab: 'style'
        },
        {
            key: 'style',
            type: 'select',
            label: 'Tab Style',
            options: [
                { label: 'Underline', value: 'underline' },
                { label: 'Pills', value: 'pills' },
                { label: 'Cards', value: 'cards' }
            ],
            default: 'underline',
            tab: 'style'
        },
        {
            key: 'activeColor',
            type: 'color',
            label: 'Highlight Color',
            default: '#4f46e5',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-12' },
                { label: 'Large', value: 'py-20' }
            ],
            default: 'py-12',
            tab: 'style'
        }
    ],
    defaultSettings: {
        tabs: [
            { id: 'tab-1', title: 'Overview', content: '<p>Overview content goes here...</p>', icon: '' },
            { id: 'tab-2', title: 'Features', content: '<p>Features content goes here...</p>', icon: '' },
            { id: 'tab-3', title: 'Pricing', content: '<p>Pricing content goes here...</p>', icon: '' }
        ],
        style: 'underline',
        width: 'max-w-4xl',
        padding: 'py-12',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
