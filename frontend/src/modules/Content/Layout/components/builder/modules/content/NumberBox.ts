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
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
    layoutSettings
} from '@/components/builder/modules/commonSettings';

/**
 * Number Box Module Definition
 */
const NumberBoxModule: ModuleDefinition = {
    name: 'numberbox',
    title: 'Number Box',
    icon: 'Hash',
    category: 'content',

    children: null,

    defaults: {
        number: '01',
        title: 'Step Title',
        description: 'Brief description of this step or number point.',
        // Layout
        layout: 'horizontal',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 20, bottom: 20, left: 20, right: 20, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 0, color: '#e0e0e0', style: 'solid' },
                top: { width: 0, color: '#e0e0e0', style: 'solid' },
                right: { width: 0, color: '#e0e0e0', style: 'solid' },
                bottom: { width: 0, color: '#e0e0e0', style: 'solid' },
                left: { width: 0, color: '#e0e0e0', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        aria_label: '',
        html_id: '',
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'content',
                label: 'Content',
                fields: [
                    { name: 'number', type: 'text', label: 'Number' },
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'description', type: 'textarea', label: 'Description' },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Number Box')
        ],
        design: [
            {
                ...layoutSettings,
                fields: [
                    ...layoutSettings.fields!,
                    {
                        name: 'layout',
                        type: 'select',
                        label: 'Layout',
                        options: [{ value: 'horizontal', label: 'Horizontal' }, { value: 'vertical', label: 'Vertical' }],
                        responsive: true
                    },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Alignment',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    }
                ]
            },
            {
                id: 'numberTypography',
                label: 'Number Typography',
                fields: [
                    { name: 'number_backgroundColor', type: 'color', label: 'Background Color' },
                    ...(typographySettings.fields as SettingDefinition[]).map(f => ({
                        ...f,
                        name: `number_${f.name}`,
                        label: `Number ${f.label}`
                    }))
                ]
            },
            {
                id: 'titleTypography',
                label: 'Title Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
                }))
            },
            {
                id: 'descriptionTypography',
                label: 'Description Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `description_${f.name}`,
                    label: `Description ${f.label}`
                }))
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

export default NumberBoxModule;
