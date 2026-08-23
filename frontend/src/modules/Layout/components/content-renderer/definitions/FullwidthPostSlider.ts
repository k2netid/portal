import type { BlockDefinition } from '@/types/builder';
import Sliders from 'lucide-vue-next/dist/esm/icons/sliders-horizontal.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidthpostslider',
    label: 'Fullwidth Post Slider',
    icon: Sliders,
    description: 'Slider specifically designed for showcasing blog posts.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthPostSliderBlock.vue')),
    settings: [
        { key: 'height', type: 'number', label: 'Height (px)', default: 600 },
        { key: 'autoplay', type: 'boolean', label: 'Autoplay', default: true },
        { key: 'autoplaySpeed', type: 'number', label: 'Autoplay Speed (ms)', default: 5000 },
        { key: 'showArrows', type: 'boolean', label: 'Show Arrows', default: true },
        { key: 'showDots', type: 'boolean', label: 'Show Dots', default: true },
        { key: 'showExcerpt', type: 'boolean', label: 'Show Excerpt', default: true },
        { key: 'showMeta', type: 'boolean', label: 'Show Meta', default: true },
        { key: 'readMoreText', type: 'text', label: 'Read More Text', default: 'Read More' },
        { key: 'overlayColor', type: 'color', label: 'Overlay Color', default: 'rgba(0,0,0,0.7)' },
        { key: 'overlayGradient', type: 'boolean', label: 'Enable Gradient Overlay', default: true }
    ],
    defaultSettings: {
        height: 600,
        autoplay: true,
        autoplaySpeed: 5000,
        showArrows: true,
        showDots: true,
        showExcerpt: true,
        showMeta: true,
        readMoreText: 'Read More',
        overlayColor: 'rgba(0,0,0,0.7)',
        overlayGradient: true,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
