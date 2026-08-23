import type { BlockDefinition } from '@/types/builder';
import MapPin from 'lucide-vue-next/dist/esm/icons/map-pin.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidthmap',
    label: 'Fullwidth Map',
    icon: MapPin,
    description: 'Large-scale interactive map integration.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthMapBlock.vue')),
    settings: [
        { key: 'address', type: 'text', label: 'Address', default: 'New York, NY' },
        { key: 'zoom', type: 'slider', label: 'Zoom', min: 1, max: 20, default: 14 },
        { key: 'height', type: 'number', label: 'Height (px)', default: 500 },
        { key: 'grayscale', type: 'boolean', label: 'Grayscale Mode', default: false },
        { key: 'showMarker', type: 'boolean', label: 'Show Marker Label', default: true },
        { key: 'markerTitle', type: 'text', label: 'Marker Title', default: 'Our Location' }
    ],
    defaultSettings: {
        address: 'New York, NY',
        zoom: 14,
        height: 500,
        grayscale: false,
        showMarker: true,
        markerTitle: 'Our Location',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
