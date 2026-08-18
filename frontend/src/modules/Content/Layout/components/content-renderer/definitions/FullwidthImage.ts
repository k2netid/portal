import type { BlockDefinition } from '@/types/builder';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidthimage',
    label: 'Fullwidth Image',
    icon: Image,
    description: 'Edge-to-edge image with optional text overlay.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthImageBlock.vue')),
    settings: [
        { key: 'image', type: 'image', label: 'Image' },
        { key: 'alt', type: 'text', label: 'Alt Text' },
        { key: 'height', type: 'number', label: 'Height (px)', default: 500 },
        {
            key: 'objectFit', type: 'select', label: 'Fit', options: [
                { label: 'Cover', value: 'cover' },
                { label: 'Contain', value: 'contain' }
            ], default: 'cover'
        },
        { key: 'showOverlay', type: 'boolean', label: 'Show Overlay', default: false },
        { key: 'overlayText', type: 'text', label: 'Overlay Text', default: '' },
        { key: 'overlayColor', type: 'color', label: 'Overlay Color', default: 'rgba(0,0,0,0.4)' },
        { key: 'caption', type: 'text', label: 'Caption', default: '' },
        { key: 'link', type: 'text', label: 'Link URL', default: '' }
    ],
    defaultSettings: {
        image: '',
        alt: '',
        height: 500,
        objectFit: 'cover',
        showOverlay: false,
        overlayText: '',
        overlayColor: 'rgba(0,0,0,0.4)',
        caption: '',
        link: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
