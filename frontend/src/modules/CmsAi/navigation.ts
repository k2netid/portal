import type { NavItem } from '@/shared/utils/navigation';

export const cmsAiNavigation: NavItem[] = [
    {
        name: 'ai-panel',
        label: 'AI Assistant',
        labelKey: 'ai.navigation.panel',
        icon: 'sparkles',
        permission: 'manage settings',
        group: 'insight',
        context: 'insight',
        priority: 70,
    },
];

export default cmsAiNavigation;
