import type { BlockDefinition } from '@/types/builder';
import Clapperboard from 'lucide-vue-next/dist/esm/icons/clapperboard.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'video',
    label: 'Video Player',
    icon: Clapperboard,
    description: 'Embed cinematic video content.',
    component: defineAsyncComponent(() => import('@/shared/blocks/VideoBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: '',
            tab: 'content'
        },
        {
            key: 'videoUrl',
            type: 'image',
            label: 'Video URL',
            default: '',
            tab: 'content'
        },
        {
            key: 'autoplay',
            type: 'boolean', // Needs boolean toggle support in PropertiesPanel
            label: 'Autoplay',
            default: false,
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: 'py-0' },
                { label: 'Medium', value: 'py-16' }
            ],
            default: 'py-16',
            tab: 'style'
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Border Radius',
            options: [
                { label: 'Medium', value: 'rounded-xl' },
                { label: 'Large', value: 'rounded-3xl' }
            ],
            default: 'rounded-3xl',
            tab: 'style'
        }
    ],
    defaultSettings: {
        title: '',
        videoUrl: '',
        autoplay: false,
        padding: 'py-16',
        bgColor: 'transparent',
        radius: 'rounded-3xl',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
