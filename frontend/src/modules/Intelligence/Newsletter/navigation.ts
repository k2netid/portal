import type { NavItem } from '@/shared/utils/navigation';

export const newsletterNavigation: NavItem[] = [
    {
        label: 'Nexus',
        labelKey: 'sharedConsole.navigation.sections.nexus',
        icon: 'activity',
        context: 'operations',
        group: 'nexus',
        priority: 90,
        children: [
            { 
                name: 'newsletter', 
                
                label: 'Newsletter', 
                labelKey: 'newsletter.navigation.menu.newsletter', 
                permission: 'view newsletter', 
                icon: 'mail',
                priority: 88
            },
            { 
                name: 'email-templates', 
                
                label: 'Email Templates', 
                labelKey: 'newsletter.navigation.menu.emailTemplates', 
                permission: 'manage settings', 
                icon: 'layout',
                priority: 87
            },
        ]
    }
];

export default newsletterNavigation;
