import {
  Tag,
} from 'lucide-vue-next';
import type { MenuItemDefinition } from '@/modules/Content/Layout/types/menu';

const definition: MenuItemDefinition = {
    name: 'category',
    label: 'layout.menus.form.types.category',
    category: 'content',
    icon: Tag,
    color: 'purple',
    description: 'Link to a category archive',
    defaultTitle: 'Category',

    dataSource: {
        endpoint: '/manage/library/categories',
        labelField: 'name',
        valueField: 'id'
    },

    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'layout.menus.form.label',
            required: true,
            placeholder: 'layout.menus.form.labelPlaceholder'
        },
        {
            key: 'target_id',
            type: 'data_select',
            label: 'layout.menus.form.selectCategory',
            required: true,
            source: '/manage/library/categories',
            labelField: 'name',
            valueField: 'id'
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
        }
    ]
};

export default definition;
