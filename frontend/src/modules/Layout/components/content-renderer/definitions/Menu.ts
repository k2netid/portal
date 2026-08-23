import type { BlockDefinition } from '@/types/builder';
import Menu from 'lucide-vue-next/dist/esm/icons/menu.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'menu',
    label: 'Navigation Menu',
    icon: Menu,
    description: 'Navigation menu with dropdowns.',
    component: defineAsyncComponent(() => import('@/shared/blocks/MenuBlock.vue')),
    settings: [
        {
            key: 'menu_id',
            type: 'data_select',
            label: 'System Menu',
            source: '/admin/ja/menus',
            description: 'Select a menu managed in the Menu Builder.',
            default: null
        },
        {
            key: 'items',
            type: 'repeater',
            label: 'Manual Menu Items',
            description: 'Use these if no System Menu is selected.',
            fields: [
                { key: 'label', type: 'text', label: 'Label' },
                { key: 'url', type: 'text', label: 'URL' },
                {
                    key: 'children',
                    type: 'repeater',
                    label: 'Submenu',
                    fields: [
                        { key: 'label', type: 'text', label: 'Label' },
                        { key: 'url', type: 'text', label: 'URL' }
                    ]
                }
            ],
            default: []
        },
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Horizontal', value: 'horizontal' },
                { label: 'Vertical', value: 'vertical' }
            ],
            default: 'horizontal'
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
            default: 'center'
        },
        { key: 'show_mobile_toggle', type: 'boolean', label: 'Show Mobile Toggle', default: true },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: '' },
                { label: 'Small', value: 'py-2' },
                { label: 'Medium', value: 'py-4' },
                { label: 'Large', value: 'py-6' }
            ],
            default: 'py-4'
        },
        { key: 'bgColor', type: 'color', label: 'Background Color', default: '' }
    ],
    defaultSettings: {
        items: [
            { label: 'Home', url: '/', children: [] },
            { label: 'About', url: '/about', children: [] },
            {
                label: 'Services', url: '/services', children: [
                    { label: 'Web Design', url: '/services/web-design' },
                    { label: 'Development', url: '/services/development' }
                ]
            },
            { label: 'Contact', url: '/contact', children: [] }
        ],
        style: 'horizontal',
        alignment: 'center',
        show_mobile_toggle: true,
        padding: 'py-4',
        bgColor: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
