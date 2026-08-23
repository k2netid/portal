import type { BlockDefinition } from '@/types/builder';
import Timer from 'lucide-vue-next/dist/esm/icons/timer.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'countdown',
    label: 'Countdown Timer',
    icon: Timer,
    description: 'Display a countdown to a specific date and time.',
    component: defineAsyncComponent(() => import('@/shared/blocks/CountdownBlock.vue')),
    settings: [
        {
            key: 'end_date',
            type: 'date',
            label: 'End Date',
            default: ''
        },
        {
            key: 'end_time',
            type: 'text',
            label: 'End Time (HH:MM)',
            default: '00:00'
        },
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: ''
        },
        {
            key: 'expired_message',
            type: 'text',
            label: 'Expired Message',
            default: 'Time is up!'
        },
        {
            key: 'show_days',
            type: 'boolean',
            label: 'Show Days',
            default: true
        },
        {
            key: 'show_hours',
            type: 'boolean',
            label: 'Show Hours',
            default: true
        },
        {
            key: 'show_minutes',
            type: 'boolean',
            label: 'Show Minutes',
            default: true
        },
        {
            key: 'show_seconds',
            type: 'boolean',
            label: 'Show Seconds',
            default: true
        },
        {
            key: 'digit_style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Boxed', value: 'boxed' },
                { label: 'Minimal', value: 'minimal' },
                { label: 'Gradient', value: 'gradient' }
            ],
            default: 'boxed'
        },
        {
            key: 'size',
            type: 'select',
            label: 'Size',
            options: [
                { label: 'Small', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Large', value: 'large' }
            ],
            default: 'large'
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'text-left' },
                { label: 'Center', value: 'text-center' },
                { label: 'Right', value: 'text-right' }
            ],
            default: 'text-center'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: '' },
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-16' },
                { label: 'Large', value: 'py-24' }
            ],
            default: 'py-16'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: ''
        }
    ],
    defaultSettings: {
        end_date: '',
        end_time: '00:00',
        title: '',
        expired_message: 'Time is up!',
        show_days: true,
        show_hours: true,
        show_minutes: true,
        show_seconds: true,
        digit_style: 'boxed',
        size: 'large',
        alignment: 'text-center',
        padding: 'py-16',
        bgColor: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
