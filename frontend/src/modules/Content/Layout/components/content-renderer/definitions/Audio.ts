import type { BlockDefinition } from '@/types/builder';
import Music from 'lucide-vue-next/dist/esm/icons/music.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'audio',
    label: 'Audio Player',
    icon: Music,
    description: 'Audio player with custom controls.',
    component: defineAsyncComponent(() => import('@/shared/blocks/AudioBlock.vue')),
    settings: [
        { key: 'src', type: 'image', label: 'Audio URL', default: '', tab: 'content' },
        { key: 'title', type: 'text', label: 'Title', default: '', tab: 'content' },
        { key: 'artist', type: 'text', label: 'Artist', default: '', tab: 'content' },
        { key: 'cover_image', type: 'image', label: 'Cover Image', default: '', tab: 'content' },
        { key: 'autoplay', type: 'boolean', label: 'Autoplay', default: false, tab: 'style' },
        { key: 'loop', type: 'boolean', label: 'Loop', default: false, tab: 'style' },
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Card', value: 'card' },
                { label: 'Minimal', value: 'minimal' },
                { label: 'Dark', value: 'dark' }
            ],
            default: 'card',
            tab: 'style'
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ],
            default: 'center',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: '' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-8' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-8',
            tab: 'style'
        }
    ],
    defaultSettings: {
        src: '',
        title: 'Audio Title',
        artist: 'Artist Name',
        cover_image: '',
        autoplay: false,
        loop: false,
        style: 'card',
        alignment: 'center',
        padding: 'py-8',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
