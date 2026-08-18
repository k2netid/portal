import type { NavItem } from '@/shared/utils/navigation';

export const publishingNavigation: NavItem[] = [
    {
        label: 'Studio',
        labelKey: 'sharedConsole.navigation.sections.studio',
        icon: 'image',
        context: 'operations',
        group: 'studio',
        priority: 100,
        children: [
            {
                name: 'div-editorial',
                type: 'divider',
                labelKey: 'sharedConsole.navigation.menu.editorial',
                priority: 100
            },
            { 
                name: 'contents.index', 
                
                label: 'Content', 
                labelKey: 'publishing.navigation.menu.studio', 
                permission: 'view content', 
                icon: 'file-text', 
                priority: 99 
            },
            { 
                name: 'comments.index', 
                
                label: 'Comments', 
                labelKey: 'publishing.navigation.menu.comments', 
                permission: 'view comments', 
                icon: 'message-square', 
                priority: 98 
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
                name: 'div-insights',
                type: 'divider',
                labelKey: 'sharedConsole.navigation.menu.insights',
                priority: 100
            },
            { 
                name: 'publishing.seo', 
                
                label: 'SEO', 
                labelKey: 'publishing.navigation.menu.seo', 
                permission: 'manage settings',
                icon: 'globe',
                priority: 98
            },
        ]
    }
];
