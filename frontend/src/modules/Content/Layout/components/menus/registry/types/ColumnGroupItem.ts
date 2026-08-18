import {
  Columns,
} from 'lucide-vue-next';
import type { MenuItemDefinition } from '@/modules/Content/Layout/types/menu';

const definition: MenuItemDefinition = {
    name: 'column_group',
    label: 'Column Group',
    category: 'structure',
    icon: Columns,
    color: 'indigo',
    description: 'Create a column group for mega menu layout',
    defaultTitle: 'Column Group',

    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Group Title / Heading',
            required: true,
            placeholder: 'Column heading'
        },
        {
            key: 'heading',
            type: 'text',
            label: 'Visual Heading',
            placeholder: 'Displayed heading text'
        },
        {
            key: 'mega_menu_column',
            type: 'number',
            label: 'Column Number',
            default: 1,
            min: 1,
            max: 6,
            description: 'Which column this group belongs to'
        },
        {
            key: 'hide_label',
            type: 'boolean',
            label: 'Hide Heading',
            default: false,
            description: 'Show only children items'
        },
        {
            key: 'show_heading_line',
            type: 'boolean',
            label: 'Show Line Under Heading',
            default: false,
            description: 'Show a separator line below the heading'
        },
        {
            key: 'css_class',
            type: 'text',
            label: 'CSS Classes',
            placeholder: 'custom-class'
        },
        {
            key: 'description',
            type: 'textarea',
            label: 'Description',
            placeholder: 'Optional description'
        }
    ]
};

export default definition;
