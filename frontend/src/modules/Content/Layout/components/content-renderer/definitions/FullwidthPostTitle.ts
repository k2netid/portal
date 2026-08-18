import type { BlockDefinition } from '@/types/builder';
import Heading from 'lucide-vue-next/dist/esm/icons/heading.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidthposttitle',
    label: 'Fullwidth Post Title',
    icon: Heading,
    description: 'Display post title and meta in a fullwidth banner.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthPostTitleBlock.vue')),
    settings: [
        { key: 'height', type: 'number', label: 'Height (px)', default: 400 },
        { key: 'showFeaturedImage', type: 'boolean', label: 'Show Featured Image', default: true },
        { key: 'overlayColor', type: 'color', label: 'Overlay Color', default: 'rgba(0,0,0,0.5)' },
        {
            key: 'contentAlignment', type: 'select', label: 'Horizontal Alignment', options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ], default: 'center'
        },
        {
            key: 'contentPosition', type: 'select', label: 'Vertical Alignment', options: [
                { label: 'Top', value: 'top' },
                { label: 'Center', value: 'center' },
                { label: 'Bottom', value: 'bottom' }
            ], default: 'center'
        },
        { key: 'showMeta', type: 'boolean', label: 'Show Meta', default: true },
        {
            key: 'tag', type: 'select', label: 'Title Tag', options: [
                { label: 'H1', value: 'h1' },
                { label: 'H2', value: 'h2' }
            ], default: 'h1'
        }
    ],
    defaultSettings: {
        height: 400,
        showFeaturedImage: true,
        overlayColor: 'rgba(0,0,0,0.5)',
        contentAlignment: 'center',
        contentPosition: 'center',
        showMeta: true,
        tag: 'h1',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
