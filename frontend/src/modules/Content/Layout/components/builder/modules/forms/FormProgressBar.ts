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

const FormProgressBarModule: ModuleDefinition = {
    name: 'form_progress',
    title: 'Progress Bar',
    icon: 'MoreHorizontal',
    category: 'forms',

    children: null,

    defaults: {
        show_percentage: true,
        show_steps: true,
        bar_color: '',
        height: 8,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 10, bottom: 10, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
    },

    settings: {
        content: [
            {
                id: 'settings',
                label: 'Progress Settings',
                fields: [
                    { name: 'show_percentage', type: 'toggle', label: 'Show Percentage' },
                    { name: 'show_steps', type: 'toggle', label: 'Show Step Numbers' },
                    { name: 'bar_color', type: 'color', label: 'Bar Color' },
                    { name: 'height', type: 'range', label: 'Bar Height', min: 2, max: 20, step: 1 }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Progress Bar')
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

export default FormProgressBarModule;
