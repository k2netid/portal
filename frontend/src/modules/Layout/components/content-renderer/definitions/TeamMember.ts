import type { BlockDefinition } from '@/modules/Layout/types/builder';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'teammember', // Matches builder module name
    component: defineAsyncComponent(() => import('@/shared/blocks/TeamMemberBlock.vue')),
    label: 'Team Member',
    icon: 'User'
} as BlockDefinition;
