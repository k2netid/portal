import type { NavItem } from '@/shared/utils/navigation';

export const mediaNavigation: NavItem[] = [
    {
        label: 'Studio',
        labelKey: 'sharedConsole.navigation.sections.studio',
        icon: 'image',
        context: 'operations',
        group: 'studio',
        priority: 80,
        children: [
            {
                name: 'div-media',
                type: 'divider',
                labelKey: 'sharedConsole.navigation.menu.media',
                priority: 80
            },
            { 
                name: 'media', 
                
                label: 'Media Library', 
                labelKey: 'common.navigation.menu.mediaLibrary', 
                permission: 'view media', 
                icon: 'image',
                priority: 79
            },
        ]
    },
    {
        label: 'Integrations & Dev',
        labelKey: 'sharedConsole.navigation.menu.devTools',
        icon: 'code',
        context: 'settings',
        group: 'integrations_dev',
        priority: 80,
        children: [
            { 
                name: 'file-manager', 
                
                label: 'File Manager', 
                labelKey: 'common.navigation.menu.fileManager', 
                permission: 'manage files', 
                icon: 'folder',
                priority: 70
            },
        ]
    }
];

export default mediaNavigation;
