import type { NavItem } from '@/shared/utils/navigation';

export const mailNavigation: NavItem[] = [
    {
        label: 'Communications',
        labelKey: 'system.navigation.sections.communications',
        icon: 'mail',
        group: 'communications',
        priority: 92,
        children: [
            {
                name: 'mail',
                label: 'JA-Mail',
                labelKey: 'mail.navigationMenuMail',
                permission: 'use mail',
                priority: 100,
                icon: 'mail',
                extension: 'mail',
            },
        ],
    },
];

export default mailNavigation;
