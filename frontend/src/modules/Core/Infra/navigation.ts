import type { NavItem } from '@/shared/utils/navigation';

export const infraNavigation: NavItem[] = [
    {
        label: 'Data Studio',
        labelKey: 'infra.models.title',
        icon: 'layers',
        context: 'operations',
        group: 'studio',
        priority: 100,
        children: [
            {
                name: 'model-index',
                label: 'Data Models',
                labelKey: 'infra.models.title',
                icon: 'database',
                permission: 'manage settings',
                priority: 100,
            },
        ],
    },
];

export default infraNavigation;
