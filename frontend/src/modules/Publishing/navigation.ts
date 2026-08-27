import type { NavItem } from '@/shared/utils/navigation';

/** Fallback when console DB menus are empty; live sidebar prefers sys_console_menus. */
export const publishingNavigation: NavItem[] = [
    {
        label: 'Editorial',
        labelKey: 'sharedConsole.navigation.menu.editorial',
        icon: 'file-text',
        group: 'editorial',
        priority: 94,
        children: [
            {
                name: 'contents.index',
                label: 'Content',
                labelKey: 'publishing.navigation.menu.studio',
                permission: 'view content',
                extension: 'publishing',
                icon: 'file-text',
                priority: 100,
            },
            {
                name: 'categories.index',
                label: 'Categories',
                labelKey: 'publishing.navigation.menu.categories',
                permission: 'view categories',
                extension: 'publishing',
                icon: 'folder',
                priority: 95,
            },
            {
                name: 'comments.index',
                label: 'Comments',
                labelKey: 'publishing.navigation.menu.comments',
                permission: 'view comments',
                extension: 'publishing',
                icon: 'message-square',
                priority: 90,
            },
            {
                name: 'publishing.seo',
                label: 'SEO',
                labelKey: 'publishing.navigation.menu.seo',
                permission: 'view seo',
                extension: 'publishing',
                icon: 'globe',
                priority: 85,
            },
            {
                name: 'publishing-settings',
                label: 'Publishing Settings',
                labelKey: 'publishing.navigation.menu.publishingSettings',
                permission: 'view settings',
                extension: 'publishing',
                icon: 'settings',
                priority: 80,
            },
        ],
    },
];

export default publishingNavigation;
