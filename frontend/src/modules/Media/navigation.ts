import type { NavItem } from '@/shared/utils/navigation';

/** Fallback when console DB menus are empty; live sidebar prefers sys_console_menus. */
export const mediaNavigation: NavItem[] = [
    {
        label: 'Editorial',
        labelKey: 'sharedConsole.navigation.menu.editorial',
        icon: 'file-text',
        group: 'editorial',
        priority: 94,
        children: [
            {
                name: 'media',
                label: 'Media Library',
                labelKey: 'sharedConsole.navigation.menu.mediaLibrary',
                permission: 'view media',
                extension: 'media',
                icon: 'image',
                priority: 88,
            },
        ],
    },
];

export default mediaNavigation;
