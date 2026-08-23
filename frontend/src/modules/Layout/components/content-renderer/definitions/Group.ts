import type { BlockDefinition } from '@/modules/Layout/types/builder';
import Boxes from 'lucide-vue-next/dist/esm/icons/boxes.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'group',
    label: 'Group',
    icon: Boxes,
    description: 'Group multiple blocks together with shared container styles.',
    component: defineAsyncComponent(() => import('@/shared/blocks/GroupBlock.vue')),
    settings: [],
    defaultSettings: {
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
