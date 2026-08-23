import type { BlockDefinition } from '@/types/builder';
import UserPlus from 'lucide-vue-next/dist/esm/icons/user-plus.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'signup',
    label: 'Signup Form',
    icon: UserPlus,
    description: 'Registration form for new users.',
    component: defineAsyncComponent(() => import('@/shared/blocks/SignupBlock.vue')),
    settings: [
        { key: 'redirectTo', type: 'text', label: 'Redirect After Signup', default: '/home' }
    ],
    defaultSettings: {
        redirectTo: '/home',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
