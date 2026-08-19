import type { NavItem } from '@/shared/utils/navigation';

export const securityNavigation: NavItem[] = [
    { 
        name: 'security-journal', 
        label: 'Security Journal', 
        labelKey: 'system.navigation.menu.securityJournal', 
        permission: 'view security logs', 
        icon: 'security-journal', 
        role: 'super', 
        group: 'journal',
        context: 'journal',
        priority: 80 
    }
];

export default securityNavigation;
