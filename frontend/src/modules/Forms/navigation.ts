import type { NavItem } from '@/shared/utils/navigation';

/** Fallback when console DB menus are empty; live sidebar prefers sys_console_menus. */
export const formsNavigation: NavItem[] = [
    {
        label: 'Audience',
        labelKey: 'forms.navigation.menu.audience',
        icon: 'users',
        group: 'audience',
        priority: 35,
        children: [
            {
                name: 'forms',
                label: 'Forms',
                labelKey: 'forms.navigation.menu.forms',
                permission: 'view forms',
                extension: 'forms',
                icon: 'clipboard-list',
                priority: 100,
            },
        ],
    },
];

export default formsNavigation;
