import type { NavItem } from '@/shared/utils/navigation';

/** Fallback when console DB menus are empty; live sidebar prefers sys_console_menus. */
export const layoutNavigation: NavItem[] = [
    {
        label: 'Editorial',
        labelKey: 'sharedConsole.navigation.menu.editorial',
        icon: 'file-text',
        group: 'editorial',
        priority: 94,
        children: [
            {
                name: 'menus',
                label: 'Menus',
                labelKey: 'layout.navigation.menu.menus',
                permission: 'view menus',
                extension: 'layout',
                icon: 'menu',
                priority: 82,
            },
            {
                name: 'widgets',
                label: 'Widgets',
                labelKey: 'layout.navigation.menu.widgets',
                permission: 'view widgets',
                extension: 'layout',
                icon: 'layers',
                priority: 80,
            },
        ],
    },
    {
        label: 'Infrastructure',
        labelKey: 'system.navigation.sections.infrastructure',
        icon: 'cpu',
        group: 'infrastructure',
        priority: 40,
        children: [
            {
                name: 'redirects',
                label: 'Redirects',
                labelKey: 'layout.navigation.menu.redirects',
                permission: 'view redirects',
                extension: 'layout',
                icon: 'undo',
                priority: 55,
            },
        ],
    },
];

export default layoutNavigation;
