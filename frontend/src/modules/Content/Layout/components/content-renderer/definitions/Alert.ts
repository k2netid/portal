import type { BlockDefinition } from '@/types/builder';
import Bell from 'lucide-vue-next/dist/esm/icons/bell.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'alert',
    label: 'Alert',
    icon: Bell,
    description: 'Info, warning, success, or error notices.',
    component: defineAsyncComponent(() => import('@/shared/blocks/AlertBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Title (Optional)',
            default: ''
        },
        {
            key: 'message',
            type: 'textarea',
            label: 'Message',
            default: 'This is an important message for your visitors.'
        },
        {
            key: 'variant',
            type: 'select',
            label: 'Type',
            options: [
                { label: 'Info', value: 'info' },
                { label: 'Warning', value: 'warning' },
                { label: 'Success', value: 'success' },
                { label: 'Error', value: 'error' },
                { label: 'Notice', value: 'notice' }
            ],
            default: 'info'
        },
        {
            key: 'dismissible',
            type: 'boolean',
            label: 'Dismissible',
            default: false
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-6' },
                { label: 'Large', value: 'py-8' }
            ],
            default: 'py-6'
        }
    ],
    defaultSettings: {
        title: '',
        message: 'This is an important message for your visitors.',
        variant: 'info',
        dismissible: false,
        width: 'max-w-4xl',
        padding: 'py-6',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
