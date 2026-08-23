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
        priority: 80,
    },
];

export default searchNavigation;
