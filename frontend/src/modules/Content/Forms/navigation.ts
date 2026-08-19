import type { NavItem } from '@/shared/utils/navigation';

export const formsNavigation: NavItem[] = [
    { 
        name: 'forms', 
        label: 'Forms', 
        labelKey: 'forms.navigation.menu.forms', 
        permission: 'view forms', 
        icon: 'clipboard-list',
        group: 'audience',
        context: 'audience',
        priority: 100
    }
];

export default formsNavigation;
