import type { BlockDefinition } from '@/types/builder';
import LayoutDashboard from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'sidebar',
    label: 'Sidebar',
    icon: LayoutDashboard,
    description: 'Sidebar with widgets.',
    component: defineAsyncComponent(() => import('@/shared/blocks/SidebarBlock.vue')),
    settings: [
        { key: 'showTitle', type: 'boolean', label: 'Show Widget Titles', default: true },
        {
            key: 'widgets',
            type: 'repeater',
            label: 'Widgets',
            itemLabel: 'title',
            fields: [
                { key: 'widgetType', type: 'select', options: [{ value: 'search', label: 'Search' }, { value: 'categories', label: 'Categories' }] },
                { key: 'title', type: 'text' },
                { key: 'count', type: 'number' }
            ],
            default: []
        }
    ],
    defaultSettings: {
        showTitle: true,
        widgets: [],
        padding: 'py-8'
    }
} as BlockDefinition;
