import type { NavItem } from '@/shared/utils/navigation';

export const securityNavigation: NavItem[] = [
    {
        label: 'Journals',
        labelKey: 'sharedConsole.navigation.menu.journals',
        icon: 'book-open',
        context: 'settings',
        group: 'observability',
        priority: 70,
        children: [
            { 
                name: 'security-journal', 
                
                label: 'Security Journal', 
                labelKey: 'system.navigation.menu.securityJournal', 
                permission: 'view security logs',
                icon: 'security-journal',
                role: 'super',
                priority: 80
            },
        ]
    }
];

export default securityNavigation;
