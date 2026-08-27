import type { NavItem } from '@/shared/utils/navigation';

export const memberNavigation: NavItem[] = [
    {
        name: 'members.index',
        label: 'Members',
        labelKey: 'member.navigation.menu.members',
        permission: 'view members',
        icon: 'user',
        group: 'audience',
        context: 'audience',
        priority: 95,
    },
];

export default memberNavigation;
