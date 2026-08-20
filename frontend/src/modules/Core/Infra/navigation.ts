import type { NavItem } from '@/shared/utils/navigation';

export const infraNavigation: NavItem[] = [
    {
        name: 'model-index',
        label: 'Data Models',
        labelKey: 'infra.models.title',
        icon: 'database',
        permission: 'manage settings',
        group: 'infrastructure',
        context: 'infrastructure',
        priority: 65,
    },
];

export default infraNavigation;
