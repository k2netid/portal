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
 * Form Textarea Module Definition
 */
const FormTextareaModule: ModuleDefinition = {
    name: 'form_textarea',
    title: 'Textarea Field',
    icon: 'TextAlignStart',
    category: 'forms',

    children: null,

    defaults: {
        label: 'Message',
        placeholder: 'Enter message...',
        field_id: 'message',
        rows: 4,
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
                    { name: 'rows', type: 'range', label: 'Rows', min: 2, max: 20, step: 1, default: 4 },
                    { name: 'placeholder', type: 'text', label: 'Placeholder' },
                    { name: 'help_text', type: 'text', label: 'Help Text' },
                    { name: 'is_required', type: 'toggle', label: 'Required' },
                ]
            },
            formVisibilitySettings,
            adminLabelSettings('Textarea Field')
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

export default FormTextareaModule;
