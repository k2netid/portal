import type { BlockDefinition } from '@/types/builder';
import Columns from 'lucide-vue-next/dist/esm/icons/columns-2.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'row',
    label: 'Row',
    icon: Columns,
    description: 'A row layout container.',
    component: defineAsyncComponent(() => import('@/shared/blocks/RowBlock.vue')),
    settings: [
        {
            key: 'columns',
            type: 'select',
            label: 'Column Layout',
            options: [
                { label: '1 Column', value: '1' },
                { label: '1/2 - 1/2', value: '1-1' },
                { label: '1/3 - 1/3 - 1/3', value: '1-1-1' },
                { label: '2/3 - 1/3', value: '2-1' },
                { label: '1/3 - 2/3', value: '1-2' },
                { label: '1/4 - 1/4 - 1/4 - 1/4', value: '1-1-1-1' }
            ],
            default: '1-1',
            tab: 'style'
        },
        {
            key: 'gutter',
            type: 'slider',
            label: 'Column Gutter',
            min: 0,
            max: 100,
            step: 4,
            unit: 'px',
            default: 30,
            tab: 'style'
        },
        {
            key: 'verticalAlign',
            type: 'toggle_group',
            label: 'Vertical Align',
            options: [
                { label: 'Top', value: 'start' },
                { label: 'Center', value: 'center' },
                { label: 'Bottom', value: 'end' },
                { label: 'Stretch', value: 'stretch' }
            ],
            default: 'stretch',
            tab: 'style'
        }
    ],
    defaultSettings: {
        columns: '1-1',
        gutter: 30,
        verticalAlign: 'stretch',
        padding: { top: '0px', right: '0', bottom: '0px', left: '0' },
        margin: { top: '0', right: '0', bottom: '0', left: '0' }
    }
} as BlockDefinition;
