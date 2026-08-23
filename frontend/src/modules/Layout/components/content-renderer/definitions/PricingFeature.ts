import type { BlockDefinition } from '@/types/builder';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'pricing_feature',
    label: 'Pricing Feature',
    icon: Check,
    description: 'A single feature line for a pricing table.',
    component: defineAsyncComponent(() => import('@/shared/blocks/PricingFeatureBlock.vue')),
    settings: [
        { key: 'text', type: 'text', label: 'Feature Text', default: 'Access to all features' },
        { key: 'included', type: 'boolean', label: 'Included', default: true }
    ],
    defaultSettings: {
        text: 'Access to all features',
        included: true,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
