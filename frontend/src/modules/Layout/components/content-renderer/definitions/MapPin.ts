import type { BlockDefinition } from '@/types/builder';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'map_pin',
    label: 'Map Pin',
    icon: MapPin,
    description: 'A custom pin point for a Map block.',
    component: defineAsyncComponent(() => import('@/shared/blocks/MapPinBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Location Name', default: 'New Point' },
        { key: 'address', type: 'text', label: 'Address/Coordinates', default: '' }
    ],
    defaultSettings: {
        title: 'New Point',
        address: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
