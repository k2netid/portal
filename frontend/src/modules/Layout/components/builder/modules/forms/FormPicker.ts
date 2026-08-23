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

const FormPickerModule: ModuleDefinition = {
    name: 'form_picker',
    title: 'Form Picker',
    icon: 'FormInput',
    category: 'forms',

    children: null,

    defaults: {
        form_slug: '',
        show_title: true,
        show_description: true,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
            styles: {
                all: { width: 0, color: '#e0e0e0', style: 'solid' }
            }
        },
    },

    settings: {
        content: [
            {
                id: 'settings',
                label: 'Settings',
                fields: [
                    {
                        name: 'form_slug',
                        type: 'select',
                        label: 'Select Form',
                        options: 'dynamic:forms',
                        searchable: true
                    },
                    {
                        name: 'show_title',
                        type: 'toggle',
                        label: 'Show Form Title',
                        default: true
                    },
                    {
                        name: 'show_description',
                        type: 'toggle',
                        label: 'Show Form Description',
                        default: true
                    }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Form Picker')
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

export default FormPickerModule;
