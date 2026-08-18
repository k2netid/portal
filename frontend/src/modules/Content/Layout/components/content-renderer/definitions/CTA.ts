import type { BlockDefinition } from '@/types/builder';
import MousePointerClick from 'lucide-vue-next/dist/esm/icons/mouse-pointer-click.js';
import { defineAsyncComponent } from 'vue';


export default {
    name: 'cta',
    label: 'Action Bar',
    icon: MousePointerClick,
    description: 'Eye-catching section with a primary button.',
    component: defineAsyncComponent(() => import('@/shared/blocks/CTABlock.vue')),
    settings: [
        { type: 'header', label: 'Content', tab: 'content' },
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: 'Start Building Today',
            tab: 'content'
        },
        {
            key: 'subtitle',
            type: 'textarea',
            label: 'Subtitle',
            default: 'Join thousands of creators using JA-Builder.',
            tab: 'content'
        },
        {
            key: 'buttonText',
            type: 'text',
            label: 'Button Text',
            default: 'Get Started Now',
            tab: 'content'
        },
        {
            key: 'buttonUrl',
            type: 'text',
            label: 'Button Link',
            default: '#',
            tab: 'content'
        },

        { type: 'header', label: 'Layout', tab: 'style' },
        {
            key: 'layout',
            type: 'select',
            label: 'Layout',
            options: [
                { label: 'Stacked (Center)', value: 'stacked-center' },
                { label: 'Stacked (Left)', value: 'stacked-left' },
                { label: 'Split (Left/Right)', value: 'split' },
                { label: 'Inline', value: 'inline' }
            ],
            default: 'stacked-center',
            responsive: true,
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Medium', value: 'py-20 px-8' },
                { label: 'Large', value: 'py-32 px-12' },
                { label: 'X-Large', value: 'py-40 px-12' }
            ],
            default: 'py-32 px-12',
            tab: 'style'
        },

        { type: 'header', label: 'Background', tab: 'style' },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: '#4f46e5',
            tab: 'style'
        },
        {
            key: 'bgImage',
            type: 'image',
            label: 'Background Image',
            default: '',
            tab: 'style'
        },
        {
            key: 'overlayOpacity',
            type: 'slider',
            label: 'Overlay Opacity',
            min: 0,
            max: 100,
            default: 0,
            tab: 'style'
        },
        {
            key: 'radius',
            type: 'toggle_group',
            label: 'Corner Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Md', value: 'rounded-2xl' },
                { label: 'Lg', value: 'rounded-3xl' }
            ],
            default: 'rounded-2xl',
            tab: 'style'
        },

        { type: 'header', label: 'Text Style', tab: 'style' },
        {
            key: 'textColor',
            type: 'color',
            label: 'Text Color',
            default: '#ffffff',
            tab: 'style'
        },

        { type: 'header', label: 'Button Style', tab: 'style' },
        {
            key: 'buttonStyle',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Primary (Solid)', value: 'primary' },
                { label: 'Secondary (White)', value: 'secondary' },
                { label: 'Outline', value: 'outline' }
            ],
            default: 'secondary',
            tab: 'style'
        },
        {
            key: 'animation',
            type: 'select',
            label: 'Animation',
            options: [
                { label: 'None', value: '' },
                { label: 'Fade Up', value: 'animate-fade-up' }
            ],
            default: '',
            tab: 'style'
        }
    ],
    defaultSettings: {
        title: 'Ready to Transform Your Workflow?',
        subtitle: 'Experience the next generation of visual building. Join over 10,000 creators today.',
        buttonText: 'Get Started for Free',
        buttonUrl: '#',
        layout: 'split',
        padding: 'py-40 px-12',
        bgColor: '#4f46e5', // This will be the base for our gradient
        bgImage: '',
        overlayOpacity: 0,
        textColor: '#ffffff',
        radius: 'rounded-none', // Section-like default
        buttonStyle: 'secondary',
        animation: 'animate-fade-up',
        blocks: [],
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
