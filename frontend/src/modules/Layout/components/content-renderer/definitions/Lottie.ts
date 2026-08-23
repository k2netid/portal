import type { BlockDefinition } from '@/modules/Layout/types/builder';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'lottie', // Must match builder module name
    component: defineAsyncComponent(() => import('@/shared/blocks/LottieBlock.vue')),
    label: 'Lottie Animation',
    icon: 'Film'
} as BlockDefinition;
