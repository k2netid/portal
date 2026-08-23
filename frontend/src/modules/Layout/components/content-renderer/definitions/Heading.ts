import type { BlockDefinition } from '@/types/builder';
import Heading from 'lucide-vue-next/dist/esm/icons/heading.js';
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'heading',
    label: 'Heading',
    icon: Heading,
    description: 'Customizable heading with typography controls.',
    component: defineAsyncComponent(() => import('@/shared/blocks/HeadingBlock.vue')),
    settings: [
        {
            key: 'text',
            type: 'text',
            label: 'Heading Text',
            default: 'Heading Text',
            tab: 'content'
        },
        {
            key: 'subtitle',
            type: 'text',
            label: 'Subtitle (Optional)',
            default: '',
            tab: 'content'
        },
        {
            key: 'tag',
            type: 'toggle_group',
            label: 'HTML Tag',
            options: [
                { label: 'H1', value: 'h1' },
                { label: 'H2', value: 'h2' },
                { label: 'H3', value: 'h3' },
                { label: 'H4', value: 'h4' },
                { label: 'H5', value: 'h5' },
                { label: 'H6', value: 'h6' }
            ],
            default: 'h2',
            tab: 'content'
        },
        {
            key: 'size',
            type: 'toggle_group',
            label: 'Size',
            options: [
                { label: 'Sm', value: 'small' },
                { label: 'Md', value: 'medium' },
                { label: 'Lg', value: 'large' },
                { label: 'XL', value: 'xlarge' },
                { label: '2XL', value: 'display' }
            ],
            default: 'large',
            tab: 'style'
        },
        {
            key: 'alignment',
            type: 'toggle_group',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left', icon: AlignLeft },
                { label: 'Center', value: 'center', icon: AlignCenter },
                { label: 'Right', value: 'right', icon: AlignRight }
            ],
            default: 'left',
            tab: 'style'
        },
        {
            key: 'decoration',
            type: 'select',
            label: 'Decoration',
            options: [
                { label: 'None', value: 'none' },
                { label: 'Underline', value: 'underline' },
                { label: 'Highlight', value: 'highlight' },
                { label: 'Gradient', value: 'gradient' }
            ],
            default: 'none',
            tab: 'style'
        },
        {
            key: 'textColor',
            type: 'color',
            label: 'Text Color',
            default: '',
            tab: 'style'
        },
        {
            key: 'width',
            type: 'select',
            label: 'Max Width',
            options: [
                { label: 'Full', value: 'max-w-full' },
                { label: 'Large', value: 'max-w-4xl' },
                { label: 'Medium', value: 'max-w-2xl' },
                { label: 'Small', value: 'max-w-xl' }
            ],
            default: 'max-w-4xl',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'None', value: 'py-0' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-8' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-8',
            tab: 'style'
        },
        // ============ ADVANCED TAB ============
        { type: 'header', label: 'Animation', tab: 'advanced' },
        {
            key: 'entranceAnimation',
            type: 'select',
            label: 'Entrance Animation',
            options: [
                { label: 'None', value: '' },
                { label: 'Fade In', value: 'animate-fade' },
                { label: 'Fade Up', value: 'animate-fade-up' },
                { label: 'Fade Down', value: 'animate-fade-down' },
                { label: 'Slide Up', value: 'animate-slide-up' },
                { label: 'Slide Down', value: 'animate-slide-down' },
                { label: 'Zoom In', value: 'animate-zoom' }
            ],
            default: '',
            tab: 'advanced'
        }
    ],
    defaultSettings: {
        text: 'Heading Text',
        subtitle: '',
        tag: 'h2',
        size: 'large',
        alignment: 'left',
        decoration: 'none',
        textColor: '',
        width: 'max-w-4xl',
        padding: 'py-8',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
