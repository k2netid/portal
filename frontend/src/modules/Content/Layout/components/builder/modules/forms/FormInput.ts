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
 * Form Input Module Definition
 */
const FormInputModule: ModuleDefinition = {
    name: 'form_input',
    title: 'Input Field',
    icon: 'Type',
    category: 'forms',

    children: null,

    defaults: {
        label: 'New Field',
        placeholder: 'Enter content...',
        field_id: 'new_field',
        type: 'text',
        is_required: false,
        help_text: '',
        // Design Defaults
        padding: { top: 0, bottom: 15, left: 0, right: 0, unit: 'px' },
        label_font_weight: '500',
        label_margin_bottom: '8px'
    },

    settings: {
        content: [
            {
                id: 'basic',
                label: 'General',
                fields: [
                    { name: 'label', type: 'text', label: 'Field Label', responsive: true },
                    {
                        name: 'field_id',
                        type: 'text',
                        label: 'Field ID (Unique Key)',
                        description: 'This is the key used for form data submission (e.g. "first_name").'
                    },
                    {
                        name: 'type',
                        type: 'select',
                        label: 'Input Type',
                        options: [
                            { value: 'text', label: 'Text' },
                            { value: 'email', label: 'Email' },
                            { value: 'tel', label: 'Phone' },
                            { value: 'url', label: 'URL' },
                            { value: 'number', label: 'Number' },
                            { value: 'password', label: 'Password' },
                        ],
                        default: 'text'
                    },
                    { name: 'placeholder', type: 'text', label: 'Placeholder' },
                    { name: 'help_text', type: 'text', label: 'Help Text' },
                    { name: 'is_required', type: 'toggle', label: 'Required' },
                ]
            },
            formVisibilitySettings,
            adminLabelSettings('Input Field')
        ],
        design: [
            {
                id: 'input_styling',
                label: 'Field Styling',
                fields: [
                    { name: 'label_text_color', type: 'color', label: 'Label Color' },
                    {
                        name: 'label_font_size',
                        type: 'dimension',
                        label: 'Label Font Size',
                        responsive: true
                    },
                    {
                        name: 'field_bg_color',
                        type: 'color',
                        label: 'Input Background'
                    }
                ]
            },
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

export default FormInputModule;
