import type { NavItem } from '@/shared/utils/navigation';

export const libraryNavigation: NavItem[] = [
    {
        label: 'Studio',
        labelKey: 'sharedConsole.navigation.sections.studio',
        icon: 'image',
        context: 'operations',
        group: 'studio',
        priority: 70,
        children: [
            {
                name: 'div-library',
                type: 'divider',
                labelKey: 'sharedConsole.navigation.menu.library',
                priority: 70
            },
            { 
                name: 'custom-fields', 
                
                label: 'Custom Fields', 
                labelKey: 'library.navigation.menu.customFields', 
                permission: 'manage content', 
                icon: 'layers',
                priority: 69
            },
            { 
                name: 'tags', 
                
                label: 'Tags', 
                labelKey: 'library.navigation.menu.tags', 
                permission: 'manage content', 
                icon: 'tags',
                priority: 68
            },
        ]
    }
];

export default libraryNavigation;
