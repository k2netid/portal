import type { BlockDefinition } from '@/types/builder';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'beforeafter', // Must match builder module name
    component: defineAsyncComponent(() => import('@/shared/blocks/BeforeAfterBlock.vue')),
    label: 'Before/After',
    icon: 'SplitSquareHorizontal'
} as BlockDefinition;
