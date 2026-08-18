import type { BlockDefinition } from '@/types/builder';
import MoveVertical from 'lucide-vue-next/dist/esm/icons/move-vertical.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'spacer',
    label: 'Spacer',
    icon: MoveVertical,
    description: 'Add vertical space between sections.',
    component: defineAsyncComponent(() => import('@/shared/blocks/SpacerBlock.vue')),
    settings: [
        {
            key: 'height',
            type: 'select',
            label: 'Preset Height',
            options: [
                { label: 'None (Use Custom)', value: '' },
                { label: 'Small (48px)', value: 'h-12' },
                { label: 'Medium (96px)', value: 'h-24' },
                { label: 'Large (192px)', value: 'h-48' },
                { label: 'Extra Large (256px)', value: 'h-64' }
            ],
            default: 'h-24'
        },
        {
            key: 'heightPx',
            type: 'slider',
            label: 'Custom Height',
            min: 0,
            max: 400,
            step: 8,
            unit: 'px',
            default: null
        },
        {
            key: 'showLine',
            type: 'boolean',
            label: 'Show Divider Line',
            default: false
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent'
        }
    ],
    defaultSettings: {
        height: 'h-24',
        heightPx: null,
        showLine: false,
        padding: 'py-0',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;

