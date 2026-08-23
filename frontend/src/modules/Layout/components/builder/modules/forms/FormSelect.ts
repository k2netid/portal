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
 * Form Select Module Definition
 */
const FormSelectModule: ModuleDefinition = {
    name: 'form_select',
    title: 'Select Field',
    icon: 'ChevronDown',
    category: 'forms',

    children: null,

    defaults: {
        label: 'Subject',
        field_id: 'subject',
        options: [
            { label: 'General Inquiry', value: 'general' },
            { label: 'Support', value: 'support' },
            { label: 'Sales', value: 'sales' }
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
                        name: 'options',
                        type: 'repeater',
                        label: 'Options',
                        itemLabel: 'label',
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
            adminLabelSettings('Select Field')
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

export default FormSelectModule;
