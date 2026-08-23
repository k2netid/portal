import type { BlockDefinition } from '@/types/builder';
import Map from 'lucide-vue-next/dist/esm/icons/map.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'map',
    label: 'Map',
    icon: Map,
    description: 'Embed Google Maps or other map services.',
    component: defineAsyncComponent(() => import('@/shared/blocks/MapBlock.vue')),
    settings: [
        {
            key: 'embedUrl',
            type: 'text',
            label: 'Map Embed URL',
            default: '',
            tab: 'content'
        },
        {
            key: 'height',
            type: 'slider',
            label: 'Map Height',
            min: 200,
            max: 600,
            step: 50,
            unit: 'px',
            default: 400,
            tab: 'style'
        },
        {
            key: 'caption',
            type: 'text',
            label: 'Caption (Optional)',
            default: '',
            tab: 'content'
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Border Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded-lg' },
                { label: 'Medium', value: 'rounded-xl' },
                { label: 'Large', value: 'rounded-2xl' }
            ],
            default: 'rounded-xl',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-8' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-8',
            tab: 'style'
        },
        {
            key: 'visibility',
            type: 'visibility',
            label: 'Visibility',
            defaultValue: { mobile: true, tablet: true, desktop: true },
            tab: 'style'
        },
    ],
    defaultSettings: {
        embedUrl: '',
        height: 400,
        caption: '',
        radius: 'rounded-xl',
        width: 'max-w-4xl',
        padding: 'py-8',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
