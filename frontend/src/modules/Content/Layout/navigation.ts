import type { NavItem } from '@/shared/utils/navigation';

export const layoutNavigation: NavItem[] = [
    {
        label: 'Studio',
        labelKey: 'sharedConsole.navigation.sections.studio',
        icon: 'image',
        context: 'operations',
        group: 'studio',
        priority: 90,
        children: [
            {
                name: 'div-design',
                type: 'divider',
                labelKey: 'sharedConsole.navigation.menu.design',
                priority: 90
            },
            {
                name: 'builder.site',
                label: 'Site Editor',
                labelKey: 'layout.navigation.menu.siteEditor',
                permission: 'manage settings',
                icon: 'layout',
                priority: 90
            },
            {
                name: 'themes', 
                
                label: 'Themes', 
                labelKey: 'publishing.navigation.menu.themes', 
                permission: 'manage themes',
                icon: 'palette',
                priority: 89
            },
            { 
                name: 'menus', 
                
                label: 'Menus', 
                labelKey: 'layout.navigation.menu.menus', 
                permission: 'view menus', 
                icon: 'menu',
                priority: 88
            },
            { 
                name: 'widgets', 
                
                label: 'Widgets', 
                labelKey: 'layout.navigation.menu.widgets', 
                permission: 'view widgets', 
                icon: 'layout',
                priority: 87
            },
        ]
    },
    {
        label: 'Nexus',
        labelKey: 'sharedConsole.navigation.sections.nexus',
        icon: 'activity',
        context: 'operations',
        group: 'nexus',
        priority: 100,
        children: [
            { 
                name: 'redirects', 
                
                label: 'Redirects', 
                labelKey: 'layout.navigation.menu.redirects', 
                permission: 'view redirects', 
                icon: 'undo',
                priority: 95
            },
        ]
    }
];

export default layoutNavigation;
