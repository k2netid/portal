import type { BlockDefinition } from '@/types/builder';
import User from 'lucide-vue-next/dist/esm/icons/user.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'author',
    label: 'Author',
    icon: User,
    description: 'Displays author information including name, bio, and social links.',
    component: defineAsyncComponent(() => import('@/shared/blocks/AuthorBlock.vue')),
    settings: [
        {
            key: 'name',
            type: 'text',
            label: 'Name',
            default: '',
            tab: 'content'
        },
        {
            key: 'title',
            type: 'text',
            label: 'Title',
            default: '',
            tab: 'content'
        },
        {
            key: 'bio',
            type: 'textarea',
            label: 'Bio',
            default: '',
            tab: 'content'
        },
        {
            key: 'image',
            type: 'image',
            label: 'Image',
            default: '',
            tab: 'content'
        },
        {
            key: 'showSocial',
            type: 'switch',
            label: 'Show Social Links',
            default: true,
            tab: 'content'
        },
        {
            key: 'layout',
            type: 'toggle_group',
            label: 'Layout',
            options: [
                { label: 'Horizontal', value: 'horizontal' },
                { label: 'Vertical', value: 'vertical' }
            ],
            default: 'horizontal',
            tab: 'style'
        },
        {
            key: 'imageSize',
            type: 'slider',
            label: 'Image Size',
            min: 40,
            max: 300,
            step: 10,
            unit: 'px',
            default: 100,
            tab: 'style'
        }
    ],
    defaultSettings: {
        name: '',
        title: '',
        bio: '',
        image: '',
        showSocial: true,
        layout: 'horizontal',
        imageSize: 100,
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
