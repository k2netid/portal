import type { ModuleDefinition } from '@/types/builder';
import {
    backgroundSettings,
    spacingSettings,
    borderSettings,
    boxShadowSettings,
    sizingSettings,
    layoutSettings,
    adminLabelSettings
} from '@/components/builder/modules/commonSettings';

const FormStepModule: ModuleDefinition = {
    name: 'form_step',
    title: 'Form Step',
    icon: 'Layers',
    category: 'forms',

    children: [], // Can contain form fields

    defaults: {
        title: 'Step 1',
        description: '',
        next_label: 'Next',
        prev_label: 'Previous',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 20, bottom: 20, left: 20, right: 20, unit: 'px' },
        margin: { top: 10, bottom: 10, left: 0, right: 0, unit: 'px' },
    },

    settings: {
        content: [
            {
                id: 'settings',
                label: 'Step Settings',
                fields: [
                    { name: 'title', type: 'text', label: 'Step Title' },
                    { name: 'description', type: 'textarea', label: 'Step Description' },
                    { name: 'next_label', type: 'text', label: 'Next Button Label' },
                    { name: 'prev_label', type: 'text', label: 'Previous Button Label' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Form Step')
        ],
        design: [
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            layoutSettings
        ],
        advanced: []
    }
};

export default FormStepModule;
