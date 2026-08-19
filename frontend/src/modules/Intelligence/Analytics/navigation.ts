import type { NavItem } from '@/shared/utils/navigation';

export const analyticsNavigation: NavItem[] = [
    {
        name: 'analytics',
        label: 'Traffic Analytics',
        labelKey: 'search.navigation.menu.analytics',
        icon: 'bar-chart',
        permission: 'manage settings',
        group: 'insight',
        context: 'insight',
        priority: 100,
    }
];

export default analyticsNavigation;
