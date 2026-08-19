import type { NavItem } from '@/shared/utils/navigation';

export const mediaNavigation: NavItem[] = [
    {
        name: 'div-media',
        type: 'divider',
        labelKey: 'sharedConsole.navigation.menu.media',
        group: 'studio',
        context: 'studio',
        priority: 82,
    },
    { 
        name: 'media', 
        label: 'Media Library', 
        labelKey: 'common.navigation.menu.mediaLibrary', 
        permission: 'view media', 
        icon: 'image',
        group: 'studio',
        context: 'studio',
        priority: 80
    },
    { 
        name: 'file-manager', 
        label: 'File Manager', 
        labelKey: 'common.navigation.menu.fileManager', 
        permission: 'manage files', 
        icon: 'folder',
        group: 'integrations',
        context: 'integrations',
        priority: 70
    }
];

export default mediaNavigation;
