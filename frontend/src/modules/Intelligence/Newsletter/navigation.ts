import type { NavItem } from '@/shared/utils/navigation';

export const newsletterNavigation: NavItem[] = [
    { 
        name: 'newsletter', 
        label: 'Newsletter', 
        labelKey: 'newsletter.navigation.menu.newsletter', 
        permission: 'view newsletter', 
        icon: 'mail',
        group: 'audience',
        context: 'audience',
        priority: 90
    },
    { 
        name: 'email-templates', 
        label: 'Email Templates', 
        labelKey: 'newsletter.navigation.menu.emailTemplates', 
        permission: 'manage settings', 
        icon: 'layout',
        group: 'audience',
        context: 'audience',
        priority: 85
    }
];

export default newsletterNavigation;
