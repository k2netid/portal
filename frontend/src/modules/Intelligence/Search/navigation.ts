import type { NavItem } from '@/shared/utils/navigation';

export const searchNavigation: NavItem[] = [
    {
        label: 'Nexus',
        labelKey: 'sharedConsole.navigation.sections.nexus',
        icon: 'activity',
        context: 'operations',
        group: 'nexus',
        priority: 100,
        children: [

            { 
                name: 'search', 
                
                label: 'Search & Indexing', 
                labelKey: 'search.navigation.menu.search', 
                icon: 'search', 
                permission: 'manage search',
                priority: 97
            },
            {
                name: 'ai-panel',
               
                label: 'AI assistant',
                labelKey: 'ai.navigation.panel',
                icon: 'sparkles',
                permission: 'manage settings',
                priority: 96,
            },
        ]
    }
];

export default searchNavigation;
