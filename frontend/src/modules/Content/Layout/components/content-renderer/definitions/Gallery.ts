import type { BlockDefinition } from '@/types/builder';
import Images from 'lucide-vue-next/dist/esm/icons/images.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'gallery',
    label: 'Image Gallery',
    icon: Images,
    description: 'Grid of images with captions.',
    component: defineAsyncComponent(() => import('@/shared/blocks/GalleryBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: 'Our Gallery',
            tab: 'content'
        },
        {
            key: 'images',
            type: 'repeater',
            label: 'Images',
            itemLabel: 'Image',
            fields: [
                { key: 'url', type: 'image', label: 'Image', default: '' },
                { key: 'caption', type: 'text', label: 'Caption', default: 'New Image' }
            ],
            default: [
                { url: '', caption: 'Project One' },
                { url: '', caption: 'Project Two' },
                { url: '', caption: 'Project Three' }
            ],
            tab: 'content'
        },
        {
            key: 'columns',
            type: 'range',
            label: 'Columns',
            min: 1,
            max: 4,
            default: 3,
            tab: 'style'
        },
        {
            key: 'gap',
            type: 'range',
            label: 'Gap',
            min: 0,
            max: 80,
            default: 24,
            tab: 'style'
        },
        {
            key: 'showCaptions',
            type: 'boolean',
            label: 'Show Captions',
            default: true,
            tab: 'style'
        },
        {
            key: 'captionPosition',
            type: 'select',
            label: 'Caption Position',
            options: [
                { label: 'Overlay (On Hover)', value: 'overlay' },
                { label: 'Below Image', value: 'below' }
            ],
            default: 'overlay',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Medium', value: 'py-16' },
                { label: 'Large', value: 'py-24' }
            ],
            default: 'py-16',
            tab: 'style'
        },
        {
            key: 'width',
            type: 'select',
            label: 'Width',
            options: [
                { label: 'Medium', value: 'max-w-6xl' },
                { label: 'Full', value: 'max-w-full' }
            ],
            default: 'max-w-6xl',
            tab: 'style'
        }
    ],
    defaultSettings: {
        title: 'Our Gallery',
        images: [
            { url: '', caption: 'Project One' },
            { url: '', caption: 'Project Two' },
            { url: '', caption: 'Project Three' }
        ],
        columns: 3,
        width: 'max-w-6xl',
        padding: 'py-16',
        bgColor: 'transparent',
        radius: 'rounded-none',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
