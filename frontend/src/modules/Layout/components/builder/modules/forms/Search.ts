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
 * Search Module Definition
 */
const SearchModule: ModuleDefinition = {
    name: 'search',
    title: 'Search',
    icon: 'Search',
    category: 'forms',

    children: null,

    defaults: {
        placeholder: 'Search...',
        buttonText: 'Search',
        showButton: true,
        buttonStyle: 'icon',
        // Background
        background: { color: '#ffffff', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 6, tr: 6, bl: 6, br: 6, linked: true }, styles: { all: { width: 1, color: '#e0e0e0', style: 'solid' }, top: { width: 1, color: '#e0e0e0', style: 'solid' }, right: { width: 1, color: '#e0e0e0', style: 'solid' }, bottom: { width: 1, color: '#e0e0e0', style: 'solid' }, left: { width: 1, color: '#e0e0e0', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        aria_label: '',
        html_id: '',
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'search',
                label: 'Search',
                fields: [
                    { name: 'placeholder', type: 'text', label: 'Placeholder Text', responsive: true },
                    { name: 'showButton', type: 'toggle', label: 'Show Button', responsive: true },
                    {
                        name: 'buttonStyle', type: 'select', label: 'Button Style', responsive: true, options: [
                            { value: 'icon', label: 'Icon Only' },
                            { value: 'text', label: 'Text Only' },
                            { value: 'both', label: 'Icon + Text' }
                        ]
                    },
                    { name: 'buttonText', type: 'text', label: 'Button Text', responsive: true },
                    { name: 'aria_label', type: 'text', label: 'ARIA Label' },
                    { name: 'html_id', type: 'text', label: 'HTML ID' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Search')
        ],
        design: [
            {
                id: 'buttonSettings',
                label: 'Button',
                fields: [
                    { name: 'buttonBackgroundColor', type: 'color', label: 'Button Background', responsive: true },
                    { name: 'buttonTextColor', type: 'color', label: 'Button Text Color', responsive: true }
                ]
            },
            {
                id: 'inputTypography',
                label: 'Input Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `input_${f.name}`,
                    label: `Input ${f.label}`
                }))
            },
            {
                id: 'buttonTypography',
                label: 'Button Typography',
                fields: (typographySettings.fields as SettingDefinition[]).map(f => ({
                    ...f,
                    name: `button_${f.name}`,
                    label: `Button ${f.label}`
                }))
            },
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            filterSettings,
            transformSettings,
            animationSettings,
            layoutSettings
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

export default SearchModule;
