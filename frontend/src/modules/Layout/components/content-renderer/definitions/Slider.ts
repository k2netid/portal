import type { BlockDefinition } from '@/types/builder';
import Image from 'lucide-vue-next/dist/esm/icons/image.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'slider',
    label: 'Fullwidth Slider',
    icon: Image,
    description: 'Image slider with autoplay and navigation.',
    component: defineAsyncComponent(() => import('@/shared/blocks/SliderBlock.vue')),
    settings: [
        {
            key: 'slides',
            type: 'repeater',
            label: 'Slides',
            itemLabel: 'Slide',
            fields: [
                { key: 'title', type: 'text', label: 'Title', default: 'Slide Title' },
                { key: 'subtitle', type: 'textarea', label: 'Subtitle', default: 'Slide Subtitle' },
                { key: 'image', type: 'image', label: 'Background Image' },
                {
                    key: 'alignment',
                    type: 'select',
                    label: 'Text Align',
                    options: [
                        { label: 'Left', value: 'left' },
                        { label: 'Center', value: 'center' },
                        { label: 'Right', value: 'right' }
                    ],
                    default: 'center'
                },
                { key: 'button_text', type: 'text', label: 'Button Text', default: 'Learn More' },
                { key: 'button_url', type: 'text', label: 'Button URL', default: '#' }
            ],
            tab: 'content'
        },
        {
            key: 'autoplay',
            type: 'boolean',
            label: 'Autoplay',
            default: true,
            tab: 'style'
        },
        {
            key: 'autoplay_speed',
            type: 'slider',
            label: 'Autoplay Speed (ms)',
            min: 2000,
            max: 10000,
            step: 500,
            default: 5000,
            tab: 'style'
        },
        {
            key: 'show_arrows',
            type: 'boolean',
            label: 'Show Arrows',
            default: true,
            tab: 'style'
        },
        {
            key: 'show_dots',
            type: 'boolean',
            label: 'Show Dots',
            default: true,
            tab: 'style'
        },
        {
            key: 'height',
            type: 'select',
            label: 'Height',
            options: [
                { label: 'Small (300px)', value: 'h-[300px]' },
                { label: 'Medium (500px)', value: 'h-[500px]' },
                { label: 'Large (700px)', value: 'h-[700px]' },
                { label: 'Full Screen', value: 'h-screen' }
            ],
            default: 'h-[500px]',
            tab: 'style'
        },
        {
            key: 'overlayEnabled',
            type: 'boolean',
            label: 'Show Overlay',
            default: true,
            tab: 'style'
        },
        {
            key: 'overlay_color',
            type: 'color',
            label: 'Overlay Color',
            default: 'rgba(0,0,0,0.4)',
            tab: 'style'
        },
        {
            key: 'slideTransition',
            type: 'select',
            label: 'Transition',
            options: [
                { label: 'Fade', value: 'fade' },
                { label: 'Slide', value: 'slide' }
            ],
            default: 'fade',
            tab: 'style'
        }
    ],
    defaultSettings: {
        slides: [
            { title: 'Welcome to Our Site', subtitle: 'Discover amazing content and services', image: '', alignment: 'center', button_text: 'Learn More', button_url: '#' },
            { title: 'Quality Products', subtitle: 'Find what you need with ease', image: '', alignment: 'center', button_text: 'Shop Now', button_url: '#' }
        ],
        autoplay: true,
        autoplay_speed: 5000,
        show_arrows: true,
        show_dots: true,
        height: 'h-[500px]',
        overlayEnabled: true,
        overlay_color: 'rgba(0,0,0,0.4)',
        slideTransition: 'fade',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
