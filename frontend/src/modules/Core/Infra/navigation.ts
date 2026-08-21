import type { NavItem } from '@/shared/utils/navigation';

export const infraNavigation: NavItem[] = [
    {
        name: 'model-index',
        label: 'Data Studio',
        labelKey: 'infra.models.title',
        icon: 'layers',
        group: 'studio',
        permission: 'manage settings',
        priority: 100,
    },
];

export default infraNavigation;
