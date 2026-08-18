import type { BlockDefinition } from '@/types/builder';
import { defineAsyncComponent } from 'vue';
export default {
    type: 'logogrid',
    name: 'Logo Grid',
    label: 'Logo Grid',
    component: defineAsyncComponent(() => import('@/shared/blocks/LogoGridBlock.vue')),
    settings: [
        { name: 'items', label: 'Items', type: 'repeater' },
        { name: 'showTitle', label: 'Show Title', type: 'boolean' },
        { name: 'title', label: 'Title', type: 'string' },
        { name: 'columns', label: 'Columns', type: 'number' },
        { name: 'gap', label: 'Gap', type: 'number' },
        { name: 'logoSize', label: 'Logo Size', type: 'number' },
        { name: 'grayscale', label: 'Grayscale', type: 'boolean' },
        { name: 'hoverColor', label: 'Hover Color', type: 'boolean' },
        { name: 'logoOpacity', label: 'Logo Opacity', type: 'number' }
    ]
} as BlockDefinition;
