import type { NavItem } from '@/shared/utils/navigation';

export const analyticsNavigation: NavItem[] = [
    {
        name: 'analytics',
        label: 'Analytics',
        labelKey: 'sharedConsole.navigation.menu.analytics',
        icon: 'bar-chart',
        permission: 'view analytics',
        group: 'insight',
        context: 'insight',
        priority: 100,
    },
];

export default analyticsNavigation;
