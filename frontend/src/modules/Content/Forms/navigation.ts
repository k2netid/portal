import type { NavItem } from '@/shared/utils/navigation';

export const formsNavigation: NavItem[] = [
    {
        label: 'Nexus',
        labelKey: 'sharedConsole.navigation.sections.nexus',
        icon: 'activity',
        context: 'operations',
        group: 'nexus',
        priority: 90,
        children: [
            {
                name: 'div-audience',
                type: 'divider',
                labelKey: 'sharedConsole.navigation.menu.audience',
                priority: 90
            },
            { 
                name: 'forms', 
                
                label: 'Forms', 
                labelKey: 'forms.navigation.menu.forms', 
                permission: 'view forms', 
                icon: 'clipboard-list',
                priority: 89
            },
        ]
    }
];

export default formsNavigation;
