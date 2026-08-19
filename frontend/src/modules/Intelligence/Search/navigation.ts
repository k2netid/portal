import type { NavItem } from '@/shared/utils/navigation';

export const searchNavigation: NavItem[] = [
    { 
        name: 'search', 
        label: 'Search & Indexing', 
        labelKey: 'search.navigation.menu.search', 
        icon: 'search', 
        permission: 'manage search',
        group: 'insight',
        context: 'insight',
        priority: 80
    },
    {
        name: 'ai-panel',
        label: 'AI assistant',
        labelKey: 'ai.navigation.panel',
        icon: 'sparkles',
        permission: 'manage settings',
        group: 'integrations',
        context: 'integrations',
        priority: 100,
    }
];

export default searchNavigation;
