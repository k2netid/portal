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
 * Form Radio Module Definition
 */
const FormRadioModule: ModuleDefinition = {
    name: 'form_radio',
    title: 'Radio Field',
    icon: 'Disc',
    category: 'forms',

    children: null,

    defaults: {
        label: 'Gender',
        field_id: 'gender',
        options: [
            { label: 'Male', value: 'male' },
            { label: 'Female', value: 'female' }
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
            adminLabelSettings('Radio Field')
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

export default FormRadioModule;
