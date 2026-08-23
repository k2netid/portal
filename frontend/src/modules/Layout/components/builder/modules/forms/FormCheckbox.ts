import type { ModuleDefinition } from '@/types/builder';
import {
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
import { formVisibilitySettings } from './formLogicSettings';

/**
 * Form Checkbox Module Definition
 */
const FormCheckboxModule: ModuleDefinition = {
    name: 'form_checkbox',
    title: 'Checkbox Field',
    icon: 'SquareCheck',
    category: 'forms',

    children: null,

    defaults: {
        label: 'Agree to Terms',
        field_id: 'terms',
        type: 'single', // single or multi
        options: [
            { label: 'Option 1', value: 'opt1' }
        ],
        is_required: false,
        help_text: '',
        padding: { top: 0, bottom: 15, left: 0, right: 0, unit: 'px' }
    },

    settings: {
        content: [
            {
                id: 'basic',
                label: 'General',
                fields: [
                    { name: 'label', type: 'text', label: 'Field Label', responsive: true },
                    { name: 'field_id', type: 'text', label: 'Field ID (Unique Key)' },
                    {
                        name: 'type',
                        type: 'select',
                        label: 'Selection Type',
                        options: [
                            { value: 'single', label: 'Single Checkbox' },
                            { value: 'multi', label: 'Checkbox Group (Multi-select)' }
                        ],
                        default: 'single'
                    },
                    {
                        name: 'options',
                        type: 'repeater',
                        label: 'Options',
                        itemLabel: 'label',
                        show_if: { field: 'type', value: 'multi' },
                        fields: [
                            { name: 'label', type: 'text', label: 'Option Label' },
                            { name: 'value', type: 'text', label: 'Option Value' }
                        ]
                    },
                    { name: 'help_text', type: 'text', label: 'Help Text' },
                    { name: 'is_required', type: 'toggle', label: 'Required' },
                ]
            },
            formVisibilitySettings,
            adminLabelSettings('Checkbox Field')
        ],
        design: [
            layoutSettings,
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            typographySettings,
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

export default FormCheckboxModule;
