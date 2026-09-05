import type { NavItem } from '@/shared/utils/navigation';

/** Fallback when console DB menus are empty; live sidebar prefers sys_console_menus. */
export const libraryNavigation: NavItem[] = [
    {
        label: 'Library',
        labelKey: 'sharedConsole.navigation.menu.library',
        icon: 'tags',
        group: 'library',
        priority: 93,
        children: [
            {
                name: 'tags',
                label: 'General Tags',
                labelKey: 'library.navigation.menu.generalTags',
                permission: 'manage tags',
                extension: 'library',
                icon: 'tags',
                priority: 100,
            },
            {
                name: 'custom-fields',
                label: 'Custom Fields',
                labelKey: 'library.navigation.menu.customFields',
                permission: 'manage tags',
                extension: 'library',
                icon: 'layers',
                priority: 95,
            },
        ],
    },
];

export default libraryNavigation;
