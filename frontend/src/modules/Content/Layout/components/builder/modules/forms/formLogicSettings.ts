import type { ModuleGroup } from '@/types/builder';

export const formVisibilitySettings: ModuleGroup = {
    id: 'form_visibility',
    label: 'Visibility & Logic',
    fields: [
        {
            name: 'visibility_mode',
            type: 'buttonGroup',
            label: 'Match Mode',
            options: [
                { label: 'All (AND)', value: 'all' },
                { label: 'Any (OR)', value: 'any' }
            ],
            default: 'all'
        },
        {
            name: 'conditions',
            type: 'conditions',
            label: 'Display Conditions',
            default: []
        }
    ]
};
