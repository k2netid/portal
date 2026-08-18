import type { BlockDefinition } from '@/types/builder';
import LogIn from 'lucide-vue-next/dist/esm/icons/log-in.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'login',
    label: 'Login Form',
    icon: LogIn,
    description: 'Add a login form to your page.',
    component: defineAsyncComponent(() => import('@/shared/blocks/LoginBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Form Title',
            default: 'Login to your account',
            responsive: true
        },
        {
            key: 'username_label',
            type: 'text',
            label: 'Username Label',
            default: 'Email Address'
        },
        {
            key: 'password_label',
            type: 'text',
            label: 'Password Label',
            default: 'Password'
        },
        {
            key: 'button_text',
            type: 'text',
            label: 'Button Text',
            default: 'Login',
            responsive: true
        },
        {
            key: 'show_forgot_password',
            type: 'boolean',
            label: 'Show Forgot Password',
            default: true
        },
        {
            key: 'show_registration',
            type: 'boolean',
            label: 'Show Registration Link',
            default: true
        },
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Clean', value: 'bg-transparent border-none p-0' },
                { label: 'Card', value: 'bg-card border shadow-sm p-8 rounded-2xl' },
                { label: 'Glass', value: 'bg-background/40 backdrop-blur-md border border-white/20 p-8 rounded-2xl shadow-xl' }
            ],
            default: 'bg-card border shadow-sm p-8 rounded-2xl'
        }
    ],
    defaultSettings: {
        title: 'Login to your account',
        username_label: 'Email Address',
        password_label: 'Password',
        button_text: 'Login',
        show_forgot_password: true,
        show_registration: true,
        style: 'bg-card border shadow-sm p-8 rounded-2xl',
        padding: 'py-20',
        width: 'max-w-md'
    }
} as BlockDefinition;
