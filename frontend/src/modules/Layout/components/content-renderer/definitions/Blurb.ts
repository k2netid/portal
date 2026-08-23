import type { BlockDefinition } from '@/types/builder';
import Info from 'lucide-vue-next/dist/esm/icons/info.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'blurb',
    label: 'Blurb',
    icon: Info,
    description: 'A versatile container for an icon, title, and description.',
    component: defineAsyncComponent(() => import('@/shared/blocks/BlurbBlock.vue')),
    settings: [
        { type: 'header', label: 'Content', tab: 'content' },
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: 'World-Class Performance',
            tab: 'content'
        },
        {
            key: 'content',
            type: 'textarea',
            label: 'Content',
            default: 'Our engine is optimized for speed, ensuring your project loads instantly anywhere in the world.',
            tab: 'content'
        },

        { type: 'header', label: 'Media', tab: 'content' },
        {
            key: 'mediaType',
            type: 'select',
            label: 'Media Type',
            options: [
                { label: 'Icon', value: 'icon' },
                { label: 'Image', value: 'image' },
                { label: 'None', value: 'none' }
            ],
            default: 'icon',
            tab: 'content'
        },
        {
            key: 'iconName',
            type: 'icon',
            label: 'Icon',
            condition: (s: Record<string, unknown>) => s.mediaType === 'icon',
            default: 'Zap',
            tab: 'content'
        },
        {
            key: 'image',
            type: 'image',
            label: 'Image',
            condition: (s: Record<string, unknown>) => s.mediaType === 'image',
            tab: 'content'
        },

        { type: 'header', label: 'Icon Style', tab: 'style' },
        {
            key: 'iconColor',
            type: 'color',
            label: 'Icon Color',
            default: '#4f46e5',
            tab: 'style'
        },
        {
            key: 'iconBgColor',
            type: 'color',
            label: 'Icon Background',
            default: 'rgba(79, 70, 229, 0.08)',
            tab: 'style'
        },
        {
            key: 'iconSize',
            type: 'slider',
            label: 'Icon Size',
            min: 24,
            max: 120,
            default: 48,
            tab: 'style'
        },
        {
            key: 'iconShape',
            type: 'select',
            label: 'Icon Shape',
            options: [
                { label: 'None', value: 'none' },
                { label: 'Circle', value: 'circle' },
                { label: 'Rounded', value: 'rounded' }
            ],
            default: 'rounded',
            tab: 'style'
        },

        { type: 'header', label: 'Layout', tab: 'style' },
        {
            key: 'iconPosition',
            type: 'select',
            label: 'Icon Position',
            options: [
                { label: 'Top', value: 'top' },
                { label: 'Left', value: 'left' }
            ],
            default: 'top',
            tab: 'style'
        },
        {
            key: 'alignment',
            type: 'toggle_group',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ],
            default: 'center',
            tab: 'style'
        }
    ],
    defaultSettings: {
        title: 'World-Class Performance',
        content: 'Our engine is optimized for speed, ensuring your project loads instantly anywhere in the world.',
        mediaType: 'icon',
        iconName: 'Zap',
        iconColor: '#4f46e5',
        iconBgColor: 'rgba(79, 70, 229, 0.08)',
        iconSize: 48,
        iconShape: 'rounded',
        iconPosition: 'top',
        alignment: 'center',
        padding: 'p-6',
        animation: 'animate-fade-up',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
