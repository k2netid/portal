import type { BlockDefinition } from '@/types/builder';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidthslider',
    label: 'Fullwidth Slider',
    icon: Image,
    description: 'Advanced fullwidth slider with child blocks.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthSliderBlock.vue')),
    settings: [
        { key: 'height', type: 'number', label: 'Height (px)', default: 600 },
        { key: 'showArrows', type: 'boolean', label: 'Show Arrows', default: true },
        { key: 'showDots', type: 'boolean', label: 'Show Dots', default: true },
        { key: 'autoplay', type: 'boolean', label: 'Autoplay', default: true },
        { key: 'autoplaySpeed', type: 'number', label: 'Autoplay Speed (ms)', default: 5000 },
        {
            key: 'contentAlignment', type: 'select', label: 'Content Alignment', options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ], default: 'center'
        },
        { key: 'overlayColor', type: 'color', label: 'Overlay Color', default: 'rgba(0,0,0,0.4)' }
    ],
    defaultSettings: {
        height: 600,
        showArrows: true,
        showDots: true,
        autoplay: true,
        autoplaySpeed: 5000,
        contentAlignment: 'center',
        overlayColor: 'rgba(0,0,0,0.4)',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
