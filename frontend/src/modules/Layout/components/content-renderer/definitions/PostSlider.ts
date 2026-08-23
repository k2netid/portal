import type { BlockDefinition } from '@/types/builder';
import Sliders from 'lucide-vue-next/dist/esm/icons/sliders-horizontal.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'postslider',
    label: 'Post Slider',
    icon: Sliders,
    description: 'Slider featuring blog posts.',
    component: defineAsyncComponent(() => import('@/shared/blocks/PostSliderBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Title', default: 'Featured Posts' },
        { key: 'totalPosts', type: 'number', label: 'Posts Count', default: 5 },
        { key: 'height', type: 'number', label: 'Height (px)', default: 500 },
        { key: 'autoplay', type: 'boolean', label: 'Autoplay', default: true },
        { key: 'autoplaySpeed', type: 'number', label: 'Autoplay Speed (ms)', default: 5000 },
        { key: 'loop', type: 'boolean', label: 'Loop Carousel', default: true },
        { key: 'showArrows', type: 'boolean', label: 'Show Arrows', default: true },
        { key: 'showDots', type: 'boolean', label: 'Show Dots', default: true },
        { key: 'showExcerpt', type: 'boolean', label: 'Show Excerpt', default: true },
        { key: 'showMeta', type: 'boolean', label: 'Show Meta', default: true },
        { key: 'overlayEnabled', type: 'boolean', label: 'Enable Overlay', default: true },
        { key: 'overlayColor', type: 'color', label: 'Overlay Color', default: 'rgba(0,0,0,0.4)' },
        { key: 'buttonText', type: 'text', label: 'Button Text', default: 'Read More' }
    ],
    defaultSettings: {
        title: 'Featured Posts',
        totalPosts: 5,
        height: 500,
        autoplay: true,
        autoplaySpeed: 5000,
        loop: true,
        showArrows: true,
        showDots: true,
        showExcerpt: true,
        showMeta: true,
        overlayEnabled: true,
        overlayColor: 'rgba(0,0,0,0.4)',
        buttonText: 'Read More',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
