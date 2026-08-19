import type { NavItem } from '@/shared/utils/navigation';

export const publishingNavigation: NavItem[] = [
    { 
        name: 'contents.index', 
        label: 'Content', 
        labelKey: 'publishing.navigation.menu.studio', 
        permission: 'view content', 
        icon: 'file-text', 
        group: 'studio',
        context: 'studio',
        priority: 100 
    },
    { 
        name: 'comments.index', 
        label: 'Comments', 
        labelKey: 'publishing.navigation.menu.comments', 
        permission: 'view comments', 
        icon: 'message-square', 
        group: 'studio',
        context: 'studio',
        priority: 95 
    },
    { 
        name: 'publishing.seo', 
        label: 'SEO', 
        labelKey: 'publishing.navigation.menu.seo', 
        permission: 'manage settings',
        icon: 'globe', 
        group: 'insight',
        context: 'insight',
        priority: 85
    }
];

export default publishingNavigation;
