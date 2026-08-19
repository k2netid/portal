import type { NavItem } from '@/shared/utils/navigation';

export const infraNavigation: NavItem[] = [
    {
        name: 'cck-index',
        label: 'Content types',
        labelKey: 'infra.cck.title',
        icon: 'layers',
        permission: 'manage settings',
        group: 'infrastructure',
        context: 'infrastructure',
        priority: 65,
    }
];

export default infraNavigation;
