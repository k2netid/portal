import type { NavItem } from '@/shared/utils/navigation';

export const libraryNavigation: NavItem[] = [
    { 
        name: 'custom-fields', 
        label: 'Custom Fields', 
        labelKey: 'library.navigation.menu.customFields', 
        permission: 'manage content', 
        icon: 'layers',
        group: 'studio',
        context: 'studio',
        priority: 75
    },
    { 
        name: 'tags', 
        label: 'Tags', 
        labelKey: 'library.navigation.menu.tags', 
        permission: 'manage content', 
        icon: 'tags',
        group: 'studio',
        context: 'studio',
        priority: 70
    }
];

export default libraryNavigation;
