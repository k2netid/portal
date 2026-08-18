import type { ModuleGroup, ModuleField } from '@/types/builder';

/**
 * Common Settings Definitions
 * Reusable property groups for all modules
 */

// Background
const generateFractions = () => {
    const common = [
        { l: '100%', v: '100%' },
        { l: '3/4', v: '75%' },
        { l: '2/3', v: '66.67%' },
        { l: '1/2', v: '50%' },
        { l: '1/3', v: '33.33%' },
        { l: '1/4', v: '25%' },
        { l: '1/5', v: '20%' },
        { l: '1/6', v: '16.67%' }
    ]
    const others = [
        { l: '11/12', v: '91.67%' },
        { l: '5/6', v: '83.33%' },
        { l: '4/5', v: '80%' },
        { l: '3/5', v: '60%' },
        { l: '2/5', v: '40%' },
        { l: '5/12', v: '41.67%' },
        { l: '7/12', v: '58.33%' },
        { l: '1/12', v: '8.33%' }
    ]

    return [
        ...common.map(i => ({ label: i.l, value: i.v, group: 'Common' })),
        ...others.map(i => ({ label: i.l, value: i.v, group: 'Other' }))
    ]
}

const widthOptions = generateFractions()

export const backgroundSettings: ModuleGroup = {
    id: 'background',
    label: 'Background',
    presets: true,
    fields: [
        {
            name: 'background',
            type: 'background',
            label: 'Background',
            responsive: true
        }
    ]
}

// Spacing
export const spacingSettings: ModuleGroup = {
    id: 'spacing',
    label: 'Spacing',
    presets: true,
    fields: [
        {
            name: 'padding',
            type: 'spacing',
            label: 'Padding',
            units: ['px', '%', 'em', 'rem', 'vh', 'vw'],
            responsive: true
        },
        {
            name: 'margin',
            type: 'spacing',
            label: 'Margin',
            units: ['px', '%', 'em', 'rem', 'vh', 'vw', 'auto'],
            responsive: true
        }
    ]
}

// Border
export const borderSettings: ModuleGroup = {
    id: 'border',
    label: 'Border',
    presets: true,
    fields: [
        {
            name: 'border',
            type: 'border',
            label: 'Border',
            responsive: true
        }
    ]
}

// Box Shadow
export const boxShadowSettings: ModuleGroup = {
    id: 'boxShadow',
    label: 'Box Shadow',
    presets: true,
    fields: [
        {
            name: 'boxShadow',
            type: 'shadow',
            label: 'Box Shadow',
            responsive: true
        }
    ]
}

// Typography (Generic)
export const typographySettings: ModuleGroup = {
    id: 'typography',
    label: 'Typography',
    presets: true,
    fields: [
        {
            name: 'font_family',
            type: 'font',
            label: 'Font Family',
            responsive: true,
            default: ''
        },
        {
            name: 'font_weight',
            type: 'select',
            label: 'Font Weight',
            responsive: true,
            options: [
                { label: 'Default', value: '' },
                { label: 'Thin (100)', value: '100' },
                { label: 'Light (300)', value: '300' },
                { label: 'Regular (400)', value: '400' },
                { label: 'Medium (500)', value: '500' },
                { label: 'Semi Bold (600)', value: '600' },
                { label: 'Bold (700)', value: '700' },
                { label: 'Extra Bold (800)', value: '800' },
                { label: 'Black (900)', value: '900' }
            ],
            default: ''
        },
        {
            name: 'font_size',
            type: 'range',
            label: 'Font Size',
            min: 8,
            max: 100,
            unit: 'px',
            responsive: true,
            default: ''
        },
        {
            name: 'line_height',
            type: 'range',
            label: 'Line Height',
            min: 0.5,
            max: 3,
            step: 0.1,
            unit: 'em',
            responsive: true,
            default: ''
        },
        {
            name: 'letter_spacing',
            type: 'range',
            label: 'Letter Spacing',
            min: -5,
            max: 10,
            step: 0.1,
            unit: 'px',
            responsive: true,
            default: ''
        },
        {
            name: 'text_align',
            type: 'buttonGroup',
            label: 'Text Alignment',
            options: [
                { value: 'left', icon: 'AlignLeft' },
                { value: 'center', icon: 'AlignCenter' },
                { value: 'right', icon: 'AlignRight' },
                { value: 'justify', icon: 'AlignJustify' }
            ],
            responsive: true,
            default: ''
        },
        {
            name: 'text_color',
            type: 'color',
            label: 'Text Color',
            responsive: true,
            default: ''
        },
        {
            name: 'text_shadow',
            type: 'shadow',
            label: 'Text Shadow',
            responsive: true
        }
    ]
}

// Animation (Moved from settings.js for consistency)
// Animation
export const animationSettings: ModuleGroup = {
    id: 'animation',
    label: 'Animation',
    presets: true,
    fields: [
        {
            name: 'animation',
            type: 'animation',
            label: 'Animation',
            responsive: true
        }
    ]
}

// CSS ID & Class Settings
export const cssSettings: ModuleGroup = {
    id: 'css',
    label: 'CSS',
    fields: [
        {
            name: 'cssId',
            type: 'text',
            label: 'CSS ID'
        },
        {
            name: 'cssClass',
            type: 'text',
            label: 'CSS Class'
        },
        {
            name: 'custom_css',
            type: 'custom_css',
            label: 'Custom CSS'
        }
    ]
}

// Sizing
export const sizingSettings: ModuleGroup = {
    id: 'sizing',
    label: 'Sizing',
    presets: true,
    fields: [
        {
            name: 'size_type',
            type: 'buttonGroup',
            label: 'Size',
            options: [
                { label: 'Auto', value: 'auto', icon: 'Minimize' },
                { label: 'Custom', value: 'custom', icon: 'Maximize' }
            ],
            default: 'auto',
            responsive: true
        },
        {
            name: 'width',
            type: 'dimension',
            label: 'Width',
            options: ['px', '%', 'em', 'rem', 'vw', 'vh', 'auto', 'inherit'],
            responsive: true,
            default: '100%',
            show_if: { field: 'size_type', value: 'custom' }
        },
        {
            name: 'max_width',
            type: 'dimension',
            label: 'Max Width',
            options: ['px', '%', 'em', 'rem', 'vw', 'vh', 'none', 'inherit'],
            responsive: true,
            default: 'none',
            show_if: { field: 'size_type', value: 'custom' }
        },
        {
            name: 'align',
            type: 'buttonGroup',
            label: 'Alignment',
            options: [
                { value: 'left', icon: 'AlignLeft' },
                { value: 'center', icon: 'AlignCenter' },
                { value: 'right', icon: 'AlignRight' }
            ],
            responsive: true,
            default: 'left',
            show_if: { field: 'size_type', value: 'custom' }
        },
        {
            name: 'min_height',
            type: 'dimension',
            label: 'Min Height',
            options: ['px', '%', 'em', 'rem', 'vh', 'auto', 'inherit'],
            responsive: true,
            default: 'auto',
            show_if: { field: 'size_type', value: 'custom' }
        },
        {
            name: 'height',
            type: 'dimension',
            label: 'Height',
            options: ['px', '%', 'em', 'rem', 'vh', 'auto', 'inherit'],
            responsive: true,
            default: 'auto',
            show_if: { field: 'size_type', value: 'custom' }
        },
        {
            name: 'max_height',
            type: 'dimension',
            label: 'Max Height',
            options: ['px', '%', 'em', 'rem', 'vh', 'none', 'inherit'],
            responsive: true,
            default: 'none',
            show_if: { field: 'size_type', value: 'custom' }
        },
        {
            name: 'column_class',
            type: 'select',
            label: 'Column Class',
            options: widthOptions,
            default: '100%',
            responsive: true,
            show_if: { field: 'size_type', value: 'custom' }
        }
    ]
}

// Filters
// Filters
export const filterSettings: ModuleGroup = {
    id: 'filters',
    label: 'Filters',
    presets: true,
    fields: [
        {
            name: 'filter',
            type: 'filters',
            label: 'Filters',
            responsive: true
        }
    ]
}

// Transform
export const transformSettings: ModuleGroup = {
    id: 'transform',
    label: 'Transform',
    presets: true,
    fields: [
        {
            name: 'transform',
            type: 'transform',
            label: 'Transform',
            responsive: true
        }
    ]
}

// Visibility
export const visibilitySettings: ModuleGroup & { fields: ModuleField[] } = {
    id: 'visibility',
    label: 'Visibility',
    fields: [
        {
            name: 'disable_on',
            type: 'buttonGroup',
            label: 'Disable On',
            multiple: true,
            options: [
                { label: 'Phone', value: 'mobile', icon: 'Smartphone' },
                { label: 'Tablet', value: 'tablet', icon: 'Tablet' },
                { label: 'Desktop', value: 'desktop', icon: 'Monitor' }
            ],
            responsive: false
        },
        {
            name: 'overflow_x',
            type: 'select',
            label: 'Horizontal Overflow',
            options: [
                { label: 'Default', value: 'visible' },
                { label: 'Visible', value: 'visible' },
                { label: 'Scroll', value: 'scroll' },
                { label: 'Hidden', value: 'hidden' },
                { label: 'Auto', value: 'auto' }
            ],
            default: 'visible',
            responsive: true
        },
        {
            name: 'overflow_y',
            type: 'select',
            label: 'Vertical Overflow',
            options: [
                { label: 'Default', value: 'visible' },
                { label: 'Visible', value: 'visible' },
                { label: 'Scroll', value: 'scroll' },
                { label: 'Hidden', value: 'hidden' },
                { label: 'Auto', value: 'auto' }
            ],
            default: 'visible',
            responsive: true
        }
    ]
}

// Position
export const positionSettings: ModuleGroup = {
    id: 'position',
    label: 'Position',
    fields: [
        {
            name: 'position',
            type: 'select',
            label: 'Position',
            options: [
                { label: 'Default', value: 'relative' },
                { label: 'Relative', value: 'relative' },
                { label: 'Absolute', value: 'absolute' },
                { label: 'Fixed', value: 'fixed' }
            ],
            default: 'relative',
            responsive: true
        },
        {
            name: 'z_index',
            type: 'advanced_number',
            label: 'Z Index',
            default: '',
            responsive: true
        },
        {
            name: 'top',
            type: 'dimension',
            label: 'Top',
            responsive: true,
            show_if: { field: 'position', value: ['absolute', 'fixed', 'relative', 'sticky'] }
        },
        {
            name: 'bottom',
            type: 'dimension',
            label: 'Bottom',
            responsive: true,
            show_if: { field: 'position', value: ['absolute', 'fixed', 'relative', 'sticky'] }
        },
        {
            name: 'left',
            type: 'dimension',
            label: 'Left',
            responsive: true,
            show_if: { field: 'position', value: ['absolute', 'fixed', 'relative', 'sticky'] }
        },
        {
            name: 'right',
            type: 'dimension',
            label: 'Right',
            responsive: true,
            show_if: { field: 'position', value: ['absolute', 'fixed', 'relative', 'sticky'] }
        }
    ]
}

// Transition
export const transitionSettings: ModuleGroup & { fields: ModuleField[] } = {
    id: 'transitions',
    label: 'Transitions',
    fields: [
        {
            name: 'transition_duration',
            type: 'range',
            label: 'Transition Duration (ms)',
            min: 0,
            max: 5000,
            step: 50,
            unit: 'ms',
            default: 0
        },
        {
            name: 'transition_delay',
            type: 'range',
            label: 'Transition Delay (ms)',
            min: 0,
            max: 5000,
            step: 50,
            unit: 'ms',
            default: 0
        },
        {
            name: 'transition_curve',
            type: 'select',
            label: 'Transition Speed Curve',
            options: [
                { label: 'Ease-In-Out', value: 'ease-in-out' },
                { label: 'Ease', value: 'ease' },
                { label: 'Ease-In', value: 'ease-in' },
                { label: 'Ease-Out', value: 'ease-out' },
                { label: 'Auto', value: 'auto' }
            ],
            default: 'ease-in-out'
        }
    ]
}

// Loop
export const loopSettings: ModuleGroup & { fields: ModuleField[] } = {
    id: 'loop',
    label: 'Loop',
    fields: [
        {
            name: 'loop_enable',
            type: 'toggle',
            label: 'Loop Element',
            default: false
        },
        {
            name: 'query_type',
            type: 'select',
            label: 'Query Type',
            options: [
                { label: 'Post Type', value: 'post_type' },
                { label: 'Terms', value: 'terms' },
                { label: 'Users', value: 'users' }
            ],
            default: 'post_type',
            show_if: { field: 'loop_enable', value: true }
        },

        // --- Post Type Mode ---
        {
            name: 'post_type',
            type: 'select',
            label: 'Post Type',
            multiple: true,
            searchable: true,
            options: [
                { label: 'Posts', value: 'post' },
                { label: 'Pages', value: 'page' },
                { label: 'Portfolio', value: 'portfolio' }
            ],
            default: ['post'],
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'include_terms',
            type: 'select',
            label: 'Only Include Posts With Specific Terms',
            multiple: true,
            searchable: true,
            options: 'dynamic:terms',
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'exclude_terms',
            type: 'select',
            label: 'Exclude Posts With Specific Terms',
            multiple: true,
            searchable: true,
            options: 'dynamic:terms',
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'include_posts',
            type: 'select',
            label: 'Only Include Specific Posts',
            multiple: true,
            searchable: true,
            options: 'dynamic:posts',
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'exclude_posts',
            type: 'select',
            label: 'Exclude Specific Posts',
            multiple: true,
            searchable: true,
            options: 'dynamic:posts',
            show_if: { field: 'query_type', value: 'post_type' }
        },

        // --- Terms Mode ---
        {
            name: 'terms',
            type: 'select',
            label: 'Terms',
            multiple: true,
            searchable: true,
            options: 'dynamic:terms',
            show_if: { field: 'query_type', value: 'terms' }
        },

        // --- Users Mode ---
        {
            name: 'users',
            type: 'select',
            label: 'Users',
            multiple: true,
            searchable: true,
            options: 'dynamic:users',
            show_if: { field: 'query_type', value: 'users' }
        },

        // --- Common Query Options ---
        {
            name: 'meta_query',
            type: 'meta_query',
            label: 'Meta Query',
            show_if: { field: 'loop_enable', value: true }
        },
        {
            name: 'order_by',
            type: 'select',
            label: 'Order By',
            options: [
                { label: 'Publish Date', value: 'date' },
                { label: 'Title', value: 'title' },
                { label: 'Random', value: 'rand' },
                { label: 'ID', value: 'ID' },
                { label: 'Modified Date', value: 'modified' },
                { label: 'Comment Count', value: 'comment_count' }
            ],
            default: 'date',
            show_if: { field: 'loop_enable', value: true }
        },
        {
            name: 'order',
            type: 'select',
            label: 'Order',
            options: [
                { label: 'Descending', value: 'DESC' },
                { label: 'Ascending', value: 'ASC' }
            ],
            default: 'DESC',
            show_if: { field: 'loop_enable', value: true }
        },

        // --- Pagination / Limits ---
        {
            name: 'posts_per_page',
            type: 'number',
            label: 'Posts Per Page',
            default: 10,
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'terms_per_page',
            type: 'number',
            label: 'Terms Per Page',
            default: 10,
            show_if: { field: 'query_type', value: 'terms' }
        },
        {
            name: 'users_per_page',
            type: 'number',
            label: 'Users Per Page',
            default: 10,
            show_if: { field: 'query_type', value: 'users' }
        },
        {
            name: 'offset',
            type: 'number',
            label: 'Post Offset',
            default: 0,
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'term_offset',
            type: 'number',
            label: 'Term Offset',
            default: 0,
            show_if: { field: 'query_type', value: 'terms' }
        },
        {
            name: 'user_offset',
            type: 'number',
            label: 'User Offset',
            default: 0,
            show_if: { field: 'query_type', value: 'users' }
        },

        // --- Additional Post Options ---
        {
            name: 'exclude_current',
            type: 'toggle',
            label: 'Exclude Current Post',
            default: false,
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'ignore_sticky',
            type: 'toggle',
            label: 'Ignore Sticky Posts',
            default: false,
            show_if: { field: 'query_type', value: 'post_type' }
        }
    ]
}


// Admin Label Settings
export const adminLabelSettings = (defaultLabel = 'Module') => ({
    id: 'admin_label',
    label: 'Admin Label',
    fields: [
        {
            name: 'admin_label',
            type: 'text',
            label: 'Admin Label',
            placeholder: defaultLabel,
            default: defaultLabel
        }
    ]
})

// Order Settings
export const orderSettings: ModuleGroup = {
    id: 'order',
    label: 'Order',
    presets: true,
    fields: [
        {
            name: 'display_order',
            type: 'advanced_number',
            label: 'Display Order',
            default: 0,
            responsive: true,
            min: -99,
            max: 99,
            step: 1
        }
    ]
}

// Link Settings
export const linkSettings: ModuleGroup = {
    id: 'link',
    label: 'Link',
    fields: [
        {
            name: 'link_url',
            type: 'text',
            label: 'Link URL'
        },
        {
            name: 'link_target',
            type: 'select',
            label: 'Link Target',
            options: [
                { label: 'Same Window', value: '_self' },
                { label: 'New Tab', value: '_blank' }
            ],
            default: '_self'
        }
    ]
}

// Layout Settings
export const layoutSettings: ModuleGroup = {
    id: 'layout',
    label: 'Layout',
    presets: true,
    fields: [
        {
            name: 'layout_type',
            type: 'select',
            label: 'Layout Style',
            options: [
                { label: 'Flex', value: 'flex' },
                { label: 'Grid', value: 'grid' },
                { label: 'Block', value: 'block' }
            ],
            default: 'flex',
            responsive: true
        },
        // Shared Gaps
        {
            name: 'gap_x',
            type: 'dimension',
            label: 'Horizontal Gap',
            default: '30px',
            responsive: true,
            options: ['px', '%', 'em', 'rem', 'vw', 'vh', 'vmin', 'vmax', 'calc', 'min', 'max', 'clamp', 'inherit', 'unset', 'normal', 'css var'],
            show_if: { field: 'layout_type', value: ['flex', 'grid'] }
        },
        {
            name: 'gap_y',
            type: 'dimension',
            label: 'Vertical Gap',
            default: '30px',
            responsive: true,
            options: ['px', '%', 'em', 'rem', 'vw', 'vh', 'vmin', 'vmax', 'calc', 'min', 'max', 'clamp', 'inherit', 'unset', 'normal', 'css var'],
            show_if: { field: 'layout_type', value: ['flex', 'grid'] }
        },

        // Grid Specific Controls
        {
            name: 'column_widths',
            type: 'select',
            label: 'Column Widths',
            options: [
                { label: 'Equal Fixed Width Columns', value: 'equal' },
                { label: 'Equal Minimum Width Columns', value: 'equal_min' },
                { label: 'Auto Width Columns', value: 'auto' },
                { label: 'Manual Width Columns', value: 'manual' }
            ],
            default: 'equal',
            responsive: true,
            show_if: { field: 'layout_type', value: 'grid' }
        },
        {
            name: 'column_min_width',
            type: 'dimension',
            label: 'Column Minimum Width',
            default: '250px',
            responsive: true,
            show_if: { field: 'layout_type', value: 'grid', AND: { field: 'column_widths', value: 'equal_min' } }
        },
        {
            name: 'grid_template_columns',
            type: 'text', // Using text for manual input like "1fr 2fr 100px"
            label: 'Grid Column Template',
            placeholder: 'e.g. 1fr 2fr 100px',
            default: '',
            responsive: true,
            show_if: { field: 'layout_type', value: 'grid', AND: { field: 'column_widths', value: 'manual' } }
        },
        {
            name: 'column_count',
            type: 'advanced_number',
            label: 'Number Of Columns',
            default: 3,
            responsive: true,
            show_if: { field: 'layout_type', value: 'grid', AND: { field: 'column_widths', value: ['equal', 'equal_min'] } }
        },
        {
            name: 'collapse_empty',
            type: 'toggle',
            label: 'Collapse Empty Columns',
            default: false,
            show_if: { field: 'layout_type', value: 'grid' }
        },
        {
            name: 'auto_columns',
            type: 'dimension',
            label: 'Grid Auto Columns',
            default: 'auto',
            options: ['px', 'fr', '%', 'min-content', 'max-content', 'auto', 'minmax'],
            responsive: true,
            show_if: { field: 'layout_type', value: 'grid' }
        },
        {
            name: 'row_heights',
            type: 'select',
            label: 'Row Heights',
            options: [
                { label: 'Auto Height Rows', value: 'auto' },
                { label: 'Custom Heights', value: 'custom' }
            ],
            default: 'auto',
            responsive: true,
            show_if: { field: 'layout_type', value: 'grid' }
        },
        {
            name: 'row_count',
            type: 'advanced_number',
            label: 'Number Of Rows',
            default: 'auto',
            responsive: true,
            show_if: { field: 'layout_type', value: 'grid' }
        },
        {
            name: 'auto_rows',
            type: 'dimension',
            label: 'Grid Auto Rows',
            default: 'auto',
            options: ['px', 'fr', '%', 'min-content', 'max-content', 'auto', 'minmax'],
            responsive: true,
            show_if: { field: 'layout_type', value: 'grid' }
        },
        {
            name: 'grid_direction',
            type: 'buttonGroup',
            label: 'Grid Direction',
            default: 'row',
            responsive: true,
            options: [
                { value: 'row', label: 'Row', icon: 'MoveRight' },
                { value: 'column', label: 'Column', icon: 'MoveDown' },
            ],
            show_if: { field: 'layout_type', value: 'grid' }
        },
        {
            name: 'grid_density',
            type: 'buttonGroup',
            label: 'Grid Density',
            default: 'dense',
            responsive: true,
            options: [
                { value: 'dense', label: 'Dense', icon: 'Grid2x2' },
                { value: 'sparse', label: 'Sparse', icon: 'LayoutGrid' },
            ],
            show_if: { field: 'layout_type', value: 'grid' }
        },


        {
            name: 'direction',
            type: 'buttonGroup',
            label: 'Layout Direction',
            default: 'column',
            responsive: true,
            options: [
                { value: 'row', label: 'Row', icon: 'ArrowRight' },
                { value: 'row-reverse', label: 'Row Reverse', icon: 'ArrowLeft' },
                { value: 'column', label: 'Column', icon: 'ArrowDown' },
                { value: 'column-reverse', label: 'Column Reverse', icon: 'ArrowUp' }
            ],
            show_if: { field: 'layout_type', value: 'flex' }
        },
        {
            name: 'justify_content',
            type: 'buttonGroup',
            label: 'Justify Content',
            default: 'flex-start',
            responsive: true,
            options: [
                { value: 'flex-start', label: 'Start', icon: 'AlignLeft' },
                { value: 'center', label: 'Center', icon: 'AlignCenter' },
                { value: 'flex-end', label: 'End', icon: 'AlignRight' },
                { value: 'space-between', label: 'Space Between', icon: 'Minimize2' },
                { value: 'space-around', label: 'Space Around', icon: 'Maximize2' },
                { value: 'space-evenly', label: 'Space Evenly', icon: 'MoreHorizontal' }
            ],
            show_if: { field: 'layout_type', value: ['flex', 'grid'] }
        },
        {
            name: 'align_items',
            type: 'buttonGroup',
            label: 'Align Items',
            default: 'stretch',
            responsive: true,
            options: [
                { value: 'flex-start', label: 'Start', icon: 'ArrowUp' },
                { value: 'center', label: 'Center', icon: 'AlignCenter' },
                { value: 'flex-end', label: 'End', icon: 'ArrowDown' },
                { value: 'stretch', label: 'Stretch', icon: 'Maximize' },
                { value: 'baseline', label: 'Baseline', icon: 'Type' }
            ],
            show_if: { field: 'layout_type', value: ['flex', 'grid'] }
        },
        {
            name: 'flex_wrap',
            type: 'buttonGroup',
            label: 'Layout Wrapping',
            default: 'nowrap',
            responsive: true,
            options: [
                { value: 'nowrap', label: 'No Wrap', icon: 'Ban' },
                { value: 'wrap', label: 'Wrap', icon: 'WrapText' },
                { value: 'wrap-reverse', label: 'Wrap Reverse', icon: 'Undo2' }
            ],
            show_if: { field: 'layout_type', value: 'flex' }
        },
        {
            name: 'align_content',
            type: 'buttonGroup',
            label: 'Align Content',
            default: 'flex-start',
            responsive: true,
            options: [
                { value: 'flex-start', label: 'Start', icon: 'ArrowUp' },
                { value: 'center', label: 'Center', icon: 'AlignCenter' },
                { value: 'flex-end', label: 'End', icon: 'ArrowDown' },
                { value: 'space-between', label: 'Space Between', icon: 'Minimize2' },
                { value: 'space-around', label: 'Space Around', icon: 'Maximize2' },
                { value: 'stretch', label: 'Stretch', icon: 'Maximize' }
            ],
            show_if: { field: 'layout_type', value: ['flex', 'grid'] }
        },
        {
            name: 'justify_items',
            type: 'buttonGroup',
            label: 'Justify Items',
            default: 'stretch',
            responsive: true,
            options: [
                { value: 'start', label: 'Start', icon: 'AlignLeft' },
                { value: 'center', label: 'Center', icon: 'AlignCenter' },
                { value: 'end', label: 'End', icon: 'AlignRight' },
                { value: 'stretch', label: 'Stretch', icon: 'Maximize' }
            ],
            show_if: { field: 'layout_type', value: 'grid' }
        }
    ]
}

// Conditions Settings
export const conditionsSettings: ModuleGroup = {
    id: 'conditions',
    label: 'Conditions',
    fields: [
        {
            name: 'conditions',
            type: 'conditions',
            label: 'Conditions'
        }
    ]
}

// Interactions Settings
export const interactionsSettings: ModuleGroup = {
    id: 'interactions',
    label: 'Interactions',
    fields: [
        {
            name: 'interactions',
            type: 'interactions',
            label: 'Interactions'
        }
    ]
}

// Scroll Effects Settings
export const scrollEffectsSettings: ModuleGroup = {
    id: 'scroll_effects',
    label: 'Scroll Effects',
    fields: [
        {
            name: 'sticky_position',
            type: 'select',
            label: 'Sticky Position',
            options: [
                { label: 'Do Not Stick', value: 'none' },
                { label: 'Stick to Top', value: 'top' },
                { label: 'Stick to Bottom', value: 'bottom' },
                { label: 'Stick to Top and Bottom', value: 'top-bottom' }
            ],
            default: 'none',
            responsive: true
        },
        {
            name: 'motion_effects_child',
            type: 'toggle',
            label: 'Apply Motion Effects To Child Elements',
            default: false
        },
        {
            name: 'scroll_effects',
            type: 'scroll_effects',
            label: 'Scroll Transform Effects'
        },
        {
            name: 'motion_trigger',
            type: 'select',
            label: 'Motion Effect Trigger',
            options: [
                { label: 'Middle of Element', value: 'middle' },
                { label: 'Top of Element', value: 'top' },
                { label: 'Bottom of Element', value: 'bottom' }
            ],
            default: 'middle'
        }
    ]
}

// Attributes Settings
export const attributesSettings: ModuleGroup = {
    id: 'attributes',
    label: 'Attributes',
    fields: [
        {
            name: 'attributes',
            type: 'attributes',
            label: 'Wrapper Attributes'
        },
        {
            name: 'title_attributes',
            type: 'attributes',
            label: 'Title Attributes'
        },
        {
            name: 'subtitle_attributes',
            type: 'attributes',
            label: 'Subtitle Attributes'
        },
        {
            name: 'button_attributes',
            type: 'attributes',
            label: 'Button Attributes'
        }
    ]
}
