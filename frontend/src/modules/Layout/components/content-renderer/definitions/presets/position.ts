import type { SettingOption, SettingDefinition, ModuleSettings } from '@/types/builder';

/**
 * Position Property Presets
 * Positioning, z-index, and layout settings
 */

import AlignStartVertical from 'lucide-vue-next/dist/esm/icons/align-start-vertical.js';
import AlignCenterVertical from 'lucide-vue-next/dist/esm/icons/align-center-vertical.js';
import AlignEndVertical from 'lucide-vue-next/dist/esm/icons/align-end-vertical.js';
import AlignStartHorizontal from 'lucide-vue-next/dist/esm/icons/align-start-horizontal.js';
import AlignCenterHorizontal from 'lucide-vue-next/dist/esm/icons/align-center-horizontal.js';
import AlignEndHorizontal from 'lucide-vue-next/dist/esm/icons/align-end-horizontal.js';
import LayoutGrid from 'lucide-vue-next/dist/esm/icons/layout-grid.js';
import Rows from 'lucide-vue-next/dist/esm/icons/rows-2.js';
import ColumnsIcon from 'lucide-vue-next/dist/esm/icons/columns-2.js';

export const positionModeOptions: SettingOption[] = [
    { label: 'Static', value: 'static' },
    { label: 'Relative', value: 'relative' },
    { label: 'Absolute', value: 'absolute' },
    { label: 'Fixed', value: 'fixed' },
    { label: 'Sticky', value: 'sticky' }
];

export const displayOptions: SettingOption[] = [
    { label: 'Block', value: 'block' },
    { label: 'Inline Block', value: 'inline-block' },
    { label: 'Flex', value: 'flex' },
    { label: 'Inline Flex', value: 'inline-flex' },
    { label: 'Grid', value: 'grid' },
    { label: 'Hidden', value: 'none' }
];

export const flexDirectionOptions: SettingOption[] = [
    { label: 'Row', value: 'row', icon: Rows },
    { label: 'Column', value: 'column', icon: ColumnsIcon }
];

export const justifyContentOptions: SettingOption[] = [
    { label: 'Start', value: 'flex-start', icon: AlignStartHorizontal },
    { label: 'Center', value: 'center', icon: AlignCenterHorizontal },
    { label: 'End', value: 'flex-end', icon: AlignEndHorizontal },
    { label: 'Between', value: 'space-between' },
    { label: 'Around', value: 'space-around' },
    { label: 'Evenly', value: 'space-evenly' }
];

export const alignItemsOptions: SettingOption[] = [
    { label: 'Start', value: 'flex-start', icon: AlignStartVertical },
    { label: 'Center', value: 'center', icon: AlignCenterVertical },
    { label: 'End', value: 'flex-end', icon: AlignEndVertical },
    { label: 'Stretch', value: 'stretch' },
    { label: 'Baseline', value: 'baseline' }
];

export const overflowOptions: SettingOption[] = [
    { label: 'Visible', value: 'visible' },
    { label: 'Hidden', value: 'hidden' },
    { label: 'Scroll', value: 'scroll' },
    { label: 'Auto', value: 'auto' }
];

/**
 * Position settings
 */
export const positionSettings: SettingDefinition[] = [
    { type: 'header', label: 'Position', tab: 'advanced' },
    {
        key: 'position',
        type: 'select',
        label: 'Position Mode',
        options: positionModeOptions,
        default: 'static',
        tab: 'advanced'
    },
    {
        key: 'zIndex',
        type: 'slider',
        label: 'Z-Index',
        min: -10,
        max: 100,
        step: 1,
        default: 0,
        tab: 'advanced'
    },
    {
        key: 'top',
        type: 'text',
        label: 'Top',
        default: 'auto',
        condition: (settings: ModuleSettings) => ['absolute', 'fixed', 'sticky'].includes(settings.position as string),
        tab: 'advanced'
    },
    {
        key: 'right',
        type: 'text',
        label: 'Right',
        default: 'auto',
        condition: (settings: ModuleSettings) => ['absolute', 'fixed'].includes(settings.position as string),
        tab: 'advanced'
    },
    {
        key: 'bottom',
        type: 'text',
        label: 'Bottom',
        default: 'auto',
        condition: (settings: ModuleSettings) => ['absolute', 'fixed'].includes(settings.position as string),
        tab: 'advanced'
    },
    {
        key: 'left',
        type: 'text',
        label: 'Left',
        default: 'auto',
        condition: (settings: ModuleSettings) => ['absolute', 'fixed'].includes(settings.position as string),
        tab: 'advanced'
    }
];

/**
 * Sizing settings
 */
export const sizingSettings: SettingDefinition[] = [
    { type: 'header', label: 'Size', tab: 'style' },
    {
        key: 'width',
        type: 'text',
        label: 'Width',
        default: 'auto',
        placeholder: 'auto, 100%, 200px',
        tab: 'style'
    },
    {
        key: 'minWidth',
        type: 'text',
        label: 'Min Width',
        default: '0',
        tab: 'style'
    },
    {
        key: 'maxWidth',
        type: 'text',
        label: 'Max Width',
        default: 'none',
        tab: 'style'
    },
    {
        key: 'height',
        type: 'text',
        label: 'Height',
        default: 'auto',
        placeholder: 'auto, 100%, 200px',
        tab: 'style'
    },
    {
        key: 'minHeight',
        type: 'text',
        label: 'Min Height',
        default: '0',
        tab: 'style'
    },
    {
        key: 'maxHeight',
        type: 'text',
        label: 'Max Height',
        default: 'none',
        tab: 'style'
    }
];

/**
 * Flexbox container settings
 */
export const flexSettings: SettingDefinition[] = [
    { type: 'header', label: 'Flex Layout', tab: 'style' },
    {
        key: 'display',
        type: 'toggle_group',
        label: 'Display',
        options: [
            { label: 'Block', value: 'block' },
            { label: 'Flex', value: 'flex' },
            { label: 'Grid', value: 'grid', icon: LayoutGrid }
        ],
        default: 'block',
        tab: 'style'
    },
    {
        key: 'flexDirection',
        type: 'toggle_group',
        label: 'Direction',
        options: flexDirectionOptions,
        default: 'row',
        condition: (settings: ModuleSettings) => settings.display === 'flex',
        tab: 'style'
    },
    {
        key: 'justifyContent',
        type: 'toggle_group',
        label: 'Justify',
        options: justifyContentOptions.slice(0, 4), // Only show first 4 with icons
        default: 'flex-start',
        condition: (settings: ModuleSettings) => settings.display === 'flex',
        tab: 'style'
    },
    {
        key: 'alignItems',
        type: 'toggle_group',
        label: 'Align',
        options: alignItemsOptions.slice(0, 4),
        default: 'flex-start',
        condition: (settings: ModuleSettings) => settings.display === 'flex',
        tab: 'style'
    },
    {
        key: 'flexWrap',
        type: 'boolean',
        label: 'Wrap Items',
        default: false,
        condition: (settings: ModuleSettings) => settings.display === 'flex',
        tab: 'style'
    },
    {
        key: 'gap',
        type: 'slider',
        label: 'Gap',
        min: 0,
        max: 80,
        step: 4,
        unit: 'px',
        default: 0,
        condition: (settings: ModuleSettings) => ['flex', 'grid'].includes(settings.display as string),
        tab: 'style'
    }
];

/**
 * Overflow settings
 */
export const overflowSettings: SettingDefinition[] = [
    {
        key: 'overflow',
        type: 'select',
        label: 'Overflow',
        options: overflowOptions,
        default: 'visible',
        tab: 'advanced'
    }
];

/**
 * All layout-related settings
 */
export const layoutSettings: SettingDefinition[] = [
    ...flexSettings,
    ...sizingSettings,
    ...positionSettings,
    ...overflowSettings
];

/**
 * Position defaults
 */
export const positionDefaults: ModuleSettings = {
    position: 'static',
    zIndex: 0,
    top: 'auto',
    right: 'auto',
    bottom: 'auto',
    left: 'auto',
    display: 'block',
    flexDirection: 'row',
    justifyContent: 'flex-start',
    alignItems: 'flex-start',
    flexWrap: false,
    gap: 0,
    width: 'auto',
    minWidth: '0',
    maxWidth: 'none',
    height: 'auto',
    minHeight: '0',
    maxHeight: 'none',
    overflow: 'visible'
};
