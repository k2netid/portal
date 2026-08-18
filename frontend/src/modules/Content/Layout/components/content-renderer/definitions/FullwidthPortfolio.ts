import type { BlockDefinition } from '@/types/builder';
import FolderOpen from 'lucide-vue-next/dist/esm/icons/folder-open.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidthportfolio',
    label: 'Fullwidth Portfolio',
    icon: FolderOpen,
    description: 'Grid or carousel display of portfolio projects.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthPortfolioBlock.vue')),
    settings: [
        { key: 'columns', type: 'number', label: 'Columns', default: 4 },
        { key: 'showTitle', type: 'boolean', label: 'Show Title', default: true },
        { key: 'showMeta', type: 'boolean', label: 'Show Meta', default: true },
        { key: 'autoplay', type: 'boolean', label: 'Autoplay', default: true },
        { key: 'autoplaySpeed', type: 'number', label: 'Autoplay Speed (ms)', default: 4000 },
        { key: 'showArrows', type: 'boolean', label: 'Show Arrows', default: true },
        { key: 'overlayColor', type: 'color', label: 'Overlay Color', default: 'rgba(0,0,0,0.6)' }
    ],
    defaultSettings: {
        columns: 4,
        showTitle: true,
        showMeta: true,
        autoplay: true,
        autoplaySpeed: 4000,
        showArrows: true,
        overlayColor: 'rgba(0,0,0,0.6)',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
