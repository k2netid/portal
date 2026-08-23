import type { BlockDefinition } from '@/types/builder';
import PlayCircle from 'lucide-vue-next/dist/esm/icons/circle-play.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'videopopup',
    label: 'Video Popup',
    icon: PlayCircle,
    description: 'Play button that opens a video in a lightbox.',
    component: defineAsyncComponent(() => import('@/shared/blocks/VideoPopupBlock.vue')),
    settings: [
        { key: 'videoUrl', type: 'text', label: 'Video URL', default: '' }
    ],
    defaultSettings: {
        videoUrl: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
