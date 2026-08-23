import type { BlockDefinition } from '@/types/builder';
import MessageSquare from 'lucide-vue-next/dist/esm/icons/message-square.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'comments',
    label: 'Post Comments',
    icon: MessageSquare,
    description: 'Display post comments and comment form.',
    component: defineAsyncComponent(() => import('@/shared/blocks/CommentsBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Title', default: 'Comments' },
        { key: 'showAvatar', type: 'boolean', label: 'Show Avatars', default: true },
        { key: 'avatarSize', type: 'number', label: 'Avatar Size (px)', default: 48 },
        { key: 'showReplyButton', type: 'boolean', label: 'Show Reply Button', default: true },
        { key: 'showForm', type: 'boolean', label: 'Show Comment Form', default: true },
        { key: 'formTitle', type: 'text', label: 'Form Title', default: 'Leave a Comment' },
        { key: 'submitText', type: 'text', label: 'Submit Button Text', default: 'Post Comment' }
    ],
    defaultSettings: {
        title: 'Comments',
        showAvatar: true,
        avatarSize: 48,
        showReplyButton: true,
        showForm: true,
        formTitle: 'Leave a Comment',
        submitText: 'Post Comment',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
