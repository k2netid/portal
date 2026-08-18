import type { BlockDefinition } from '@/types/builder';
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'contactform', // Matches builder module name
    label: 'Contact Form',
    icon: Mail,
    description: 'A customizable contact form for lead generation.',
    component: defineAsyncComponent(() => import('@/shared/blocks/ContactFormBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Form Title',
            default: 'Get in Touch',
            responsive: true
        },
        {
            key: 'description',
            type: 'textarea',
            label: 'Description',
            default: 'Fill out the form below and we will get back to you as soon as possible.',
            responsive: true
        },
        {
            key: 'emailTo',
            type: 'text',
            label: 'Email To',
            placeholder: 'admin@example.com',
            default: ''
        },
        {
            key: 'successMessage',
            type: 'text',
            label: 'Success Message',
            default: 'Thank you! Your message has been sent.'
        },
        {
            key: 'buttonText',
            type: 'text',
            label: 'Button Text',
            default: 'Send Message',
            responsive: true
        },
        {
            key: 'fields',
            type: 'repeater',
            label: 'Form Fields',
            itemLabel: 'label',
            fields: [
                {
                    key: 'label',
                    type: 'text',
                    label: 'Field Label',
                    default: 'New Field'
                },
                {
                    key: 'type',
                    type: 'select',
                    label: 'Field Type',
                    options: [
                        { label: 'Text', value: 'text' },
                        { label: 'Email', value: 'email' },
                        { label: 'Textarea', value: 'textarea' },
                        { label: 'Select', value: 'select' },
                        { label: 'Checkbox', value: 'checkbox' }
                    ],
                    default: 'text'
                },
                {
                    key: 'required',
                    type: 'boolean',
                    label: 'Required',
                    default: true
                },
                {
                    key: 'width',
                    type: 'select',
                    label: 'Width',
                    options: [
                        { label: 'Full Width', value: 'w-full' },
                        { label: 'Half Width', value: 'w-full md:w-[calc(50%-1rem)]' }
                    ],
                    default: 'w-full'
                }
            ],
            default: [
                { label: 'Name', type: 'text', required: true, width: 'w-full md:w-[calc(50%-1rem)]' },
                { label: 'Email', type: 'email', required: true, width: 'w-full md:w-[calc(50%-1rem)]' },
                { label: 'Message', type: 'textarea', required: true, width: 'w-full' }
            ]
        },
        {
            key: 'style',
            type: 'select',
            label: 'Form Style',
            options: [
                { label: 'Clean', value: 'bg-transparent border-none p-0' },
                { label: 'Card', value: 'bg-card border shadow-sm p-8 rounded-2xl' },
                { label: 'Glassmorphism', value: 'bg-background/40 backdrop-blur-md border border-white/20 p-8 rounded-2xl shadow-xl' }
            ],
            default: 'bg-card border shadow-sm p-8 rounded-2xl'
        }
    ],
    defaultSettings: {
        title: 'Get in Touch',
        description: 'Fill out the form below and we will get back to you as soon as possible.',
        buttonText: 'Send Message',
        fields: [
            { label: 'Name', type: 'text', required: true, width: 'w-full md:w-[calc(50%-1rem)]' },
            { label: 'Email', type: 'email', required: true, width: 'w-full md:w-[calc(50%-1rem)]' },
            { label: 'Message', type: 'textarea', required: true, width: 'w-full' }
        ],
        style: 'bg-card border shadow-sm p-8 rounded-2xl',
        padding: 'py-20',
        width: 'max-w-3xl'
    }
} as BlockDefinition;
