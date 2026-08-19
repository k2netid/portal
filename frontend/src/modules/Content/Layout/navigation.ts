import type { NavItem } from '@/shared/utils/navigation';

export const layoutNavigation: NavItem[] = [
    {
        name: 'div-design',
        type: 'divider',
        labelKey: 'sharedConsole.navigation.menu.design',
        group: 'studio',
        context: 'studio',
        priority: 92,
    },
    {
        name: 'builder.site',
        label: 'Site Editor',
        labelKey: 'layout.navigation.menu.siteEditor',
        permission: 'manage settings',
        icon: 'layout',
        group: 'studio',
        context: 'studio',
        priority: 90,
    },
    {
        name: 'themes',
        label: 'Themes',
        labelKey: 'publishing.navigation.menu.themes',
        permission: 'manage themes',
        icon: 'palette',
        group: 'studio',
        context: 'studio',
        priority: 88,
    },
    {
        name: 'menus',
        label: 'Menus',
        labelKey: 'layout.navigation.menu.menus',
        permission: 'view menus',
        icon: 'menu',
        group: 'studio',
        context: 'studio',
        priority: 86,
    },
    {
        name: 'widgets',
        label: 'Widgets',
        labelKey: 'layout.navigation.menu.widgets',
        permission: 'view widgets',
        icon: 'layers',
        group: 'studio',
        context: 'studio',
        priority: 84,
    },
    {
        name: 'redirects',
        label: 'Redirects',
        labelKey: 'layout.navigation.menu.redirects',
        permission: 'view redirects',
        icon: 'undo',
        group: 'infrastructure',
        context: 'infrastructure',
        priority: 60,
    },
];

export default layoutNavigation;
