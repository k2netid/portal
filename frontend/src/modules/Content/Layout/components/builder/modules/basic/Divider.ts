import type { ModuleDefinition, SettingDefinition } from '@/types/builder';
import {
    backgroundSettings,
    spacingSettings,
    borderSettings,
    boxShadowSettings,
    sizingSettings,
    filterSettings,
    transformSettings,
    animationSettings,
    visibilitySettings,
    positionSettings,
    transitionSettings,
    cssSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
    layoutSettings,
    typographySettings,
    linkSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Divider Module Definition
 */
const DividerModule: ModuleDefinition = {
    name: 'divider',
    title: 'Divider',
    icon: 'Minus',
    category: 'basic',

    children: null,

    defaults: {
        visible: true,
        pattern: 'classic',
        lineWeight: 2,
        lineColor: '#cccccc',
        lineWidth: '100%',
        alignment: 'center',
        use_gradient: false,
        layout_type: 'block',
        gap_x: '0px',
        gap_y: '0px',
        // Icon/Text Elements
        add_icon: false,
        icon: 'Star',
        icon_size: '24px',
        icon_color: '#333333',
        add_text: false,
        text: 'Divider',
        text_gap: '15px',
        divider_element_position: 'center', // left, center, right
        padding: { top: 20, bottom: 20, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' }
    },

    settings: {
        content: [
            {
                id: 'divider_elements',
                label: 'Elements',
                fields: [
                    { name: 'visible', type: 'toggle', label: 'Show Divider Line', default: true },
                    {
                        name: 'icon_group',
                        type: 'group',
                        label: 'Icon',
                        fields: [
                            { name: 'add_icon', type: 'toggle', label: 'Add Icon' },
                            { name: 'icon', type: 'icon', label: 'Select Icon', show_if: { field: 'add_icon', value: true } }
                        ]
                    },
                    {
                        name: 'text_group',
                        type: 'group',
                        label: 'Text',
                        fields: [
                            { name: 'add_text', type: 'toggle', label: 'Add Text' },
                            { name: 'text', type: 'text', label: 'Divider Text', show_if: { field: 'add_text', value: true } }
                        ]
                    }
                ]
            },
            linkSettings,
            backgroundSettings,
            adminLabelSettings('Divider')
        ],
        design: [
            layoutSettings,
            {
                id: 'style',
                label: 'Line Style',
                fields: [
                    {
                        name: 'pattern', type: 'select', label: 'Line Pattern', responsive: true, options: [
                            { value: 'classic', label: 'Classic Solid' },
                            { value: 'waves', label: 'Modern Waves' },
                            { value: 'zigzag', label: 'Sharp Zig-zag' },
                            { value: 'dots', label: 'Elegant Dots' },
                            { value: 'dashed', label: 'Standard Dashed' }
                        ]
                    },
                    { name: 'lineWeight', type: 'range', label: 'Thickness', min: 1, max: 20, step: 1, unit: 'px', responsive: true },
                    { name: 'lineColor', type: 'color', label: 'Line Color', responsive: true, show_if: { field: 'use_gradient', value: false } },
                    { name: 'use_gradient', type: 'toggle', label: 'Enable Gradient Line' },
                    { name: 'gradient', type: 'gradient', label: 'Line Gradient', show_if: { field: 'use_gradient', value: true } },
                    { name: 'lineWidth', type: 'dimension', label: 'Line Width', responsive: true },
                    {
                        name: 'alignment', type: 'buttonGroup', label: 'Line Alignment', responsive: true, options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    }
                ]
            },
            {
                id: 'element_styling',
                label: 'Icon & Text Style',
                show_if: [
                    { field: 'add_icon', value: true },
                    { field: 'add_text', value: true }
                ],
                fields: [
                    {
                        name: 'divider_element_position',
                        type: 'select',
                        label: 'Element Position',
                        options: [
                            { label: 'Left', value: 'left' },
                            { label: 'Center', value: 'center' },
                            { label: 'Right', value: 'right' }
                        ]
                    },
                    { name: 'text_gap', type: 'dimension', label: 'Gap Around Element', default: '15px' },
                    {
                        name: 'icon_styling',
                        type: 'group',
                        label: 'Icon Styling',
                        show_if: { field: 'add_icon', value: true },
                        fields: [
                            { name: 'icon_size', type: 'dimension', label: 'Icon Size' },
                            { name: 'icon_color', type: 'color', label: 'Icon Color' }
                        ]
                    },
                    {
                        name: 'text_styling',
                        type: 'group',
                        label: 'Text Styling',
                        show_if: { field: 'add_text', value: true },
                        fields: typographySettings.fields as SettingDefinition[]
                    }
                ]
            },
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            filterSettings,
            transformSettings,
            animationSettings
        ],
        advanced: [
            visibilitySettings,
            positionSettings,
            transitionSettings,
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
};

export default DividerModule;
