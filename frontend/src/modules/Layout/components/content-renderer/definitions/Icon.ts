import type { BlockDefinition } from '@/modules/Layout/types/builder';
import Sparkles from 'lucide-vue-next/dist/esm/icons/sparkles.js';
import AlignLeft from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenter from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignRight from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'icon',
    label: 'Icon',
    icon: Sparkles,
    description: 'Display a single icon with styling options.',
    component: defineAsyncComponent(() => import('@/shared/blocks/IconBlock.vue')),
    settings: [
        {
            key: 'icon',
            type: 'select',
            label: 'Icon',
            options: [
                { label: '⭐ Star', value: 'star' },
                { label: '❤️ Heart', value: 'heart' },
                { label: '✓ Check', value: 'check' },
                { label: '✕ X', value: 'x' },
                { label: '➕ Plus', value: 'plus' },
                { label: '➖ Minus', value: 'minus' },
                { label: '→ Arrow Right', value: 'arrow-right' },
                { label: '← Arrow Left', value: 'arrow-left' },
                { label: '↑ Arrow Up', value: 'arrow-up' },
                { label: '↓ Arrow Down', value: 'arrow-down' },
                { label: '✉️ Mail', value: 'mail' },
                { label: '📞 Phone', value: 'phone' },
                { label: '📍 Map Pin', value: 'map-pin' },
                { label: '🌐 Globe', value: 'globe' },
                { label: '📅 Calendar', value: 'calendar' },
                { label: '⏰ Clock', value: 'clock' },
                { label: '👤 User', value: 'user' },
                { label: '👥 Users', value: 'users' },
                { label: '⚙️ Settings', value: 'settings' },
                { label: '🏠 Home', value: 'home' },
                { label: '🔍 Search', value: 'search' },
                { label: '🔔 Bell', value: 'bell' },
                { label: '🔖 Bookmark', value: 'bookmark' },
                { label: '📷 Camera', value: 'camera' },
                { label: '⬇️ Download', value: 'download' },
                { label: '⬆️ Upload', value: 'upload' },
                { label: '📤 Share', value: 'share' },
                { label: '👁️ Eye', value: 'eye' },
                { label: '✏️ Edit', value: 'edit' },
                { label: '🗑️ Trash', value: 'trash' },
                { label: '🔗 Link', value: 'link' },
                { label: '▶️ Play', value: 'play' },
                { label: '⏸️ Pause', value: 'pause' },
                { label: '🔊 Volume', value: 'volume' },
                { label: '🎤 Mic', value: 'mic' },
                { label: '🖼️ Image', value: 'image' },
                { label: '🎬 Video', value: 'video' },
                { label: '📄 File', value: 'file' },
                { label: '📁 Folder', value: 'folder' },
                { label: '☁️ Cloud', value: 'cloud' },
                { label: '🗄️ Database', value: 'database' },
                { label: '💻 Code', value: 'code' },
                { label: '🛡️ Shield', value: 'shield' },
                { label: '🔒 Lock', value: 'lock' },
                { label: '🔑 Key', value: 'key' },
                { label: '🏆 Trophy', value: 'trophy' },
                { label: '⚡ Zap', value: 'zap' },
                { label: '☀️ Sun', value: 'sun' },
                { label: '🌙 Moon', value: 'moon' },
                { label: '✨ Sparkles', value: 'sparkles' },
                { label: '🔥 Flame', value: 'flame' }
            ],
            default: 'star',
            tab: 'content'
        },
        {
            key: 'size',
            type: 'toggle_group',
            label: 'Size',
            options: [
                { label: 'Sm', value: 'small' },
                { label: 'Md', value: 'medium' },
                { label: 'Lg', value: 'large' },
                { label: 'XL', value: 'xlarge' }
            ],
            default: 'medium',
            tab: 'style'
        },
        {
            key: 'shape',
            type: 'toggle_group',
            label: 'Background Shape',
            options: [
                { label: 'None', value: 'none' },
                { label: 'Circle', value: 'circle' },
                { label: 'Round', value: 'rounded' },
                { label: 'Square', value: 'square' }
            ],
            default: 'none',
            tab: 'style'
        },
        {
            key: 'alignment',
            type: 'toggle_group',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left', icon: AlignLeft },
                { label: 'Center', value: 'center', icon: AlignCenter },
                { label: 'Right', value: 'right', icon: AlignRight }
            ],
            default: 'center',
            tab: 'style'
        },
        {
            key: 'iconColor',
            type: 'color',
            label: 'Icon Color',
            default: '',
            tab: 'style'
        },
        {
            key: 'iconBgColor',
            type: 'color',
            label: 'Background Color',
            default: '',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'toggle_group',
            label: 'Section Padding',
            options: [
                { label: 'None', value: 'py-0' },
                { label: 'Sm', value: 'py-4' },
                { label: 'Md', value: 'py-8' },
                { label: 'Lg', value: 'py-12' }
            ],
            default: 'py-8',
            tab: 'style'
        }
    ],
    defaultSettings: {
        icon: 'star',
        size: 'medium',
        shape: 'none',
        alignment: 'center',
        iconColor: '',
        iconBgColor: '',
        padding: 'py-8',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
