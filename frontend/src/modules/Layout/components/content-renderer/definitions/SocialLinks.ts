import type { BlockDefinition } from '@/types/builder';
import Share2 from 'lucide-vue-next/dist/esm/icons/share-2.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'sociallinks',
    label: 'Social Media Follow',
    icon: Share2,
    description: 'Add icons linking to your social media profiles.',
    component: defineAsyncComponent(() => import('@/shared/blocks/SocialLinksBlock.vue')),
    settings: [
        {
            key: 'links',
            type: 'repeater',
            label: 'Platforms',
            itemLabel: 'Platform',
            fields: [
                {
                    key: 'platform',
                    type: 'select',
                    label: 'Platform',
                    options: [
                        { label: 'Facebook', value: 'facebook' },
                        { label: 'Twitter / X', value: 'twitter' },
                        { label: 'Instagram', value: 'instagram' },
                        { label: 'LinkedIn', value: 'linkedin' },
                        { label: 'YouTube', value: 'youtube' },
                        { label: 'GitHub', value: 'github' },
                        { label: 'TikTok', value: 'tiktok' }
                    ]
                },
                { key: 'url', type: 'text', label: 'URL' },
                { key: 'color', type: 'color', label: 'Custom Color' }
            ],
            tab: 'content'
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'start' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'end' }
            ],
            tab: 'style'
        },
        { key: 'gap', type: 'text', label: 'Gap (e.g. 16px)', tab: 'style' },
        { key: 'showLabels', type: 'boolean', label: 'Show Labels', default: false, tab: 'style' },
        { key: 'icon_size', type: 'text', label: 'Icon Size (e.g. 24px)', tab: 'style' },
        {
            key: 'shape',
            type: 'select',
            label: 'Icon Shape',
            options: [
                { label: 'Square', value: 'square' },
                { label: 'Rounded', value: 'rounded' },
                { label: 'Circle', value: 'circle' }
            ],
            tab: 'style'
        },
        {
            key: 'style',
            type: 'select',
            label: 'Icon Style',
            options: [
                { label: 'Filled', value: 'filled' },
                { label: 'Outline', value: 'outline' },
                { label: 'Minimal', value: 'minimal' }
            ],
            tab: 'style'
        }
    ],
    defaultSettings: {
        links: [
            { platform: 'facebook', url: '#', color: '#1877F2' },
            { platform: 'twitter', url: '#', color: '#1DA1F2' },
            { platform: 'instagram', url: '#', color: '#E4405F' }
        ],
        alignment: 'center',
        icon_size: '24px',
        shape: 'rounded',
        style: 'filled'
    }
} as BlockDefinition;
