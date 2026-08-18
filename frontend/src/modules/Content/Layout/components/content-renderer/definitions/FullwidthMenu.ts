import type { BlockDefinition } from '@/types/builder';
import Menu from 'lucide-vue-next/dist/esm/icons/menu.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidthmenu',
    label: 'Fullwidth Menu',
    icon: Menu,
    description: 'Wide navigation menu with brand logo.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthMenuBlock.vue')),
    settings: [
        { key: 'showLogo', type: 'boolean', label: 'Show Logo', default: true },
        { key: 'logoImage', type: 'image', label: 'Logo Image' },
        { key: 'logoText', type: 'text', label: 'Logo Text (Fallback)', default: 'Logo' },
        { key: 'logoMaxHeight', type: 'number', label: 'Logo Max Height (px)', default: 60 },
        {
            key: 'alignment', type: 'select', label: 'Menu Alignment', options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ], default: 'right'
        },
        { key: 'itemSpacing', type: 'number', label: 'Item Spacing (px)', default: 24 }
    ],
    defaultSettings: {
        showLogo: true,
        logoText: 'Logo',
        logoMaxHeight: 60,
        alignment: 'right',
        itemSpacing: 24,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
