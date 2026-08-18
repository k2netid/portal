import type { NavItem } from '@/shared/utils/navigation';

export const analyticsNavigation: NavItem[] = [
    {
        label: 'Nexus',
        labelKey: 'sharedConsole.navigation.sections.nexus',
        icon: 'activity',
        context: 'operations',
        group: 'nexus',
        priority: 100,
        children: [
            {
                name: 'analytics',
               
                label: 'Traffic Analytics',
                labelKey: 'search.navigation.menu.analytics',
                icon: 'bar-chart',
                permission: 'manage settings',
                priority: 99,
            }
        ]
    }
];

export default analyticsNavigation;
