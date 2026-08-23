import type { BlockDefinition } from '@/types/builder';
import ImageIcon from 'lucide-vue-next/dist/esm/icons/image.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'logo',
    label: 'Logo',
    icon: ImageIcon,
    description: 'Display an image or text logo.',
    component: defineAsyncComponent(() => import('@/shared/blocks/LogoBlock.vue')),
    settings: [
        { key: 'type', type: 'select', label: 'Type', options: [{ label: 'Image', value: 'image' }, { label: 'Text', value: 'text' }], default: 'image' },
        { key: 'src', type: 'image', label: 'Logo Image', default: '', condition: (s: Record<string, unknown>) => s.type === 'image' },
        { key: 'text', type: 'text', label: 'Logo Text', default: 'Brand', condition: (s: Record<string, unknown>) => s.type === 'text' }
    ],
    defaultSettings: {
        type: 'image',
        src: '',
        text: 'Brand',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
