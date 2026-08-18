import type { BlockDefinition } from '@/types/builder';
import Layout from 'lucide-vue-next/dist/esm/icons/layout-dashboard.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'fullwidthheader',
    label: 'Fullwidth Header',
    icon: Layout,
    description: 'Hero-style header with dual buttons and flexible alignment.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FullwidthHeaderBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Title', default: 'Welcome to Our Website' },
        { key: 'subtitle', type: 'textarea', label: 'Subtitle', default: 'We create amazing digital experiences' },
        { key: 'buttonText', type: 'text', label: 'Button 1 Text', default: 'Get Started' },
        { key: 'buttonUrl', type: 'text', label: 'Button 1 URL', default: '#' },
        { key: 'showButton2', type: 'boolean', label: 'Show Button 2', default: true },
        { key: 'button2Text', type: 'text', label: 'Button 2 Text', default: 'Learn More' },
        { key: 'button2Url', type: 'text', label: 'Button 2 URL', default: '#' },
        { key: 'height', type: 'number', label: 'Height (px)', default: 400 },
        {
            key: 'contentAlignment', type: 'select', label: 'Vertical Alignment', options: [
                { label: 'Top', value: 'top' },
                { label: 'Center', value: 'center' },
                { label: 'Bottom', value: 'bottom' }
            ], default: 'center'
        },
        {
            key: 'textAlignment', type: 'select', label: 'Horizontal Alignment', options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ], default: 'center'
        },
        { key: 'overlayColor', type: 'color', label: 'Overlay Color', default: 'rgba(0,0,0,0.4)' }
    ],
    defaultSettings: {
        title: 'Welcome to Our Website',
        subtitle: 'We create amazing digital experiences',
        buttonText: 'Get Started',
        buttonUrl: '#',
        showButton2: true,
        button2Text: 'Learn More',
        button2Url: '#',
        height: 400,
        contentAlignment: 'center',
        textAlignment: 'center',
        overlayColor: 'rgba(0,0,0,0.4)',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
