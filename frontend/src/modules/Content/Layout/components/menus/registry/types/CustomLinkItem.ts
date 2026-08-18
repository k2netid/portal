import {
  Link,
} from 'lucide-vue-next';
import type { MenuItemDefinition } from '@/modules/Content/Layout/types/menu';

const definition: MenuItemDefinition = {
    name: 'custom',
    label: 'layout.menus.form.types.link',
    category: 'basic',
    icon: Link,
    color: 'emerald',
    description: 'Add a custom URL link',
    defaultTitle: 'New Link',

    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'layout.menus.form.label',
            required: true,
            placeholder: 'layout.menus.form.labelPlaceholder'
        },
        {
            key: 'url',
            type: 'text',
            label: 'layout.menus.form.url',
            default: '#',
            placeholder: 'https://...'
        },
        {
            key: 'open_in_new_tab',
            type: 'boolean',
            label: 'layout.menus.form.openInNewTab',
            default: false
        },
        {
            key: 'icon',
            type: 'icon_picker',
            label: 'layout.menus.form.icon',
            default: null
        },
        {
            key: 'css_class',
            type: 'text',
            label: 'layout.menus.form.cssClasses',
            placeholder: 'layout.menus.form.placeholders.cssClasses'
        },
        // Mega menu settings
        {
            key: 'mega_menu_layout',
            type: 'select',
            label: 'layout.menus.form.megaMenuLayout',
            options: [
                { label: 'layout.menus.form.options.default', value: 'default' },
                { label: 'layout.menus.form.options.mega', value: 'mega' }
            ],
            default: 'default',
            group: 'mega_menu'
        },
        {
            key: 'mega_menu_column',
            type: 'number',
            label: 'layout.menus.form.columnNumber',
            default: 0,
            min: 0,
            max: 6,
            group: 'mega_menu'
        },
        {
            key: 'heading',
            type: 'text',
            label: 'layout.menus.form.columnHeading',
            placeholder: 'Optional heading text',
            group: 'mega_menu'
        },
        {
            key: 'hide_label',
            type: 'boolean',
            label: 'layout.menus.form.hideLabel',
            default: false,
            group: 'mega_menu'
        },
        // Badge
        {
            key: 'badge',
            type: 'text',
            label: 'layout.menus.form.badgeText',
            placeholder: 'New',
            group: 'badge'
        },
        {
            key: 'badge_color',
            type: 'select',
            label: 'layout.menus.form.badgeColor',
            options: [
                { label: 'layout.menus.form.options.primary', value: 'primary' },
                { label: 'layout.menus.form.options.secondary', value: 'secondary' },
                { label: 'layout.menus.form.options.success', value: 'success' },
                { label: 'layout.menus.form.options.warning', value: 'warning' },
                { label: 'layout.menus.form.options.danger', value: 'danger' }
            ],
            default: 'primary',
            group: 'badge'
        },
        // Image
        {
            key: 'image',
            type: 'media',
            label: 'layout.menus.form.image',
            group: 'appearance'
        },
        {
            key: 'image_size',
            type: 'select',
            label: 'layout.menus.form.imageSize',
            options: [
                { label: 'layout.menus.form.options.auto169', value: 'auto' },
                { label: 'layout.menus.form.options.landscape_sm', value: 'landscape_sm' },
                { label: 'layout.menus.form.options.landscape_md', value: 'landscape_md' },
                { label: 'layout.menus.form.options.landscape_lg', value: 'landscape_lg' },
                { label: 'layout.menus.form.options.portrait_sm', value: 'portrait_sm' },
                { label: 'layout.menus.form.options.portrait_md', value: 'portrait_md' },
                { label: 'layout.menus.form.options.full169', value: 'full' }
            ],
            default: 'auto',
            group: 'appearance'
        },
        {
            key: 'description',
            type: 'textarea',
            label: 'layout.menus.form.promotionDescription',
            placeholder: 'Add a quote or short description over the image',
            group: 'appearance'
        }
    ]
};

export default definition;
