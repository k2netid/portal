import type { NavItem } from '@/shared/utils/navigation';

export const infraNavigation: NavItem[] = [
    {
        label: 'Identity & Integrations',
        labelKey: 'system.navigation.menu.identityIntegrations',
        icon: 'code',
        context: 'settings',
        group: 'integrations_dev',
        priority: 75,
        children: [
            {
                name: 'cck-index',
               
                label: 'Content types',
                labelKey: 'infra.cck.title',
                icon: 'layers',
                permission: 'manage settings',
                priority: 75,
            },
        ],
    },
];

export default infraNavigation;
