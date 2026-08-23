import type { BlockDefinition } from '@/types/builder';
import Quote from 'lucide-vue-next/dist/esm/icons/quote.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'testimonial',
    label: 'Testimonial',
    icon: Quote,
    description: 'A stylish quote from a customer or client.',
    component: defineAsyncComponent(() => import('@/shared/blocks/TestimonialBlock.vue')),
    settings: [
        { type: 'header', label: 'Content', tab: 'content' },
        {
            key: 'quote',
            type: 'textarea',
            label: 'Quote Text',
            default: 'This platform has completely transformed how we build our web presence.',
            responsive: true,
            tab: 'content'
        },
        {
            key: 'rating',
            type: 'slider',
            label: 'Rating (Stars)',
            min: 0,
            max: 5,
            step: 0.5,
            default: 5,
            tab: 'content'
        },

        { type: 'header', label: 'Author', tab: 'content' },
        {
            key: 'author',
            type: 'text',
            label: 'Name',
            default: 'Sarah Johnson',
            responsive: true,
            tab: 'content'
        },
        {
            key: 'job_title',
            type: 'text',
            label: 'Job Title',
            default: 'CEO',
            responsive: true,
            tab: 'content'
        },
        {
            key: 'company_name',
            type: 'text',
            label: 'Company Name',
            default: 'Techflow',
            responsive: true,
            tab: 'content'
        },
        {
            key: 'avatar',
            type: 'image',
            label: 'Avatar',
            default: '',
            tab: 'content'
        },
        {
            key: 'companyLogo',
            type: 'image',
            label: 'Company Logo',
            default: '',
            tab: 'content'
        },

        { type: 'header', label: 'Card Style', tab: 'style' },
        {
            key: 'cardBgColor',
            type: 'color',
            label: 'Background',
            default: '#ffffff',
            tab: 'style'
        },
        {
            key: 'cardBorderColor',
            type: 'color',
            label: 'Border',
            default: 'rgba(0,0,0,0.05)',
            tab: 'style'
        },
        {
            key: 'cardShadow',
            type: 'select',
            label: 'Shadow',
            options: [
                { label: 'None', value: 'shadow-none' },
                { label: 'Small', value: 'shadow-sm' },
                { label: 'Medium', value: 'shadow-md' },
                { label: 'Large', value: 'shadow-xl' }
            ],
            default: 'shadow-sm',
            tab: 'style'
        },
        {
            key: 'radius',
            type: 'toggle_group',
            label: 'Radius',
            options: [
                { label: 'Sm', value: 'rounded-lg' },
                { label: 'Md', value: 'rounded-2xl' },
                { label: 'Lg', value: 'rounded-3xl' }
            ],
            default: 'rounded-2xl',
            tab: 'style'
        },
        {
            key: 'alignment',
            type: 'toggle_group',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'text-left' },
                { label: 'Center', value: 'text-center' }
            ],
            default: 'text-center',
            responsive: true,
            tab: 'style'
        },

        { type: 'header', label: 'Quote Style', tab: 'style' },
        {
            key: 'quoteColor',
            type: 'color',
            label: 'Text Color',
            default: '#1e293b', // slate-800
            tab: 'style'
        },
        {
            key: 'quoteSize',
            type: 'slider',
            label: 'Font Size',
            min: 14,
            max: 32,
            step: 1,
            unit: 'px',
            default: 18,
            tab: 'style'
        },

        { type: 'header', label: 'Icon', tab: 'style' },
        {
            key: 'showQuoteIcon',
            type: 'boolean',
            label: 'Show Icon',
            default: true,
            tab: 'style'
        },
        {
            key: 'quoteIconColor',
            type: 'color',
            label: 'Icon Color',
            default: '#e2e8f0', // slate-200
            tab: 'style'
        },

        { type: 'header', label: 'Animation', tab: 'style' },
        {
            key: 'animation',
            type: 'select',
            label: 'Animation',
            options: [
                { label: 'None', value: '' },
                { label: 'Fade In', value: 'animate-fade' },
                { label: 'Fade Up', value: 'animate-fade-up' }
            ],
            default: '',
            tab: 'style'
        }
    ],
    defaultSettings: {
        quote: 'This platform has completely transformed how we build our web presence. The attention to detail and ease of use is simply unparalleled.',
        rating: 5,
        author: 'Sarah Johnson',
        job_title: 'Product Director',
        company_name: 'Techflow Creative',
        alignment: 'text-center',
        cardBgColor: '#ffffff',
        cardBorderColor: 'transparent',
        cardShadow: 'shadow-xl',
        radius: 'rounded-3xl',
        quoteColor: '#0f172a',
        quoteSize: 20,
        showQuoteIcon: true,
        quoteIconColor: 'rgba(15, 23, 42, 0.05)',
        animation: 'animate-fade-up'
    }
} as BlockDefinition;
