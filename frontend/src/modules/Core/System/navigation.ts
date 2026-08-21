import type { NavItem } from '@/shared/utils/navigation';

export const systemNavigation: NavItem[] = [
    {
        label: 'Users & Access',
        labelKey: 'system.navigation.sections.usersAccess',
        icon: 'users',
        group: 'identity',
        priority: 95,
        children: [
            { name: 'kyc-reviews', label: 'KYC Reviews', labelKey: 'system.navigation.menu.kycReviews', permission: 'manage kyc reviews', icon: 'users' },
            { name: 'users.index', label: 'Users', labelKey: 'system.navigation.menu.users', permission: 'view users', icon: 'users' },
            { name: 'roles', label: 'Roles & Permissions', labelKey: 'system.navigation.menu.roles', permission: 'view roles', icon: 'roles' },
        ],
    },
    {
        label: 'Journals',
        labelKey: 'sharedConsole.navigation.menu.journals',
        icon: 'book-open',
        group: 'observability',
        priority: 90,
        children: [
            {
                name: 'journal-dashboard',
                label: 'Journal Dashboard',
                labelKey: 'system.navigation.menu.journalDashboard',
                permission: 'view logs',
                icon: 'journal-dashboard',
                role: 'super',
                priority: 100,
            },
            {
                name: 'activity-journal',
                label: 'Activity Journal',
                labelKey: 'system.navigation.menu.activityJournal',
                permission: 'view activity logs',
                icon: 'activity-journal',
                role: 'super',
                priority: 90,
            },
            {
                name: 'system-journal',
                label: 'System Journal',
                labelKey: 'system.navigation.menu.systemJournal',
                permission: 'view system',
                icon: 'system-journal',
                role: 'super',
                priority: 70,
            },
            {
                name: 'access-journal',
                label: 'Access History',
                labelKey: 'system.navigation.menu.accessJournal',
                permission: 'view users',
                icon: 'access-journal',
                role: 'super',
                priority: 60,
            },
        ],
    },
    {
        label: 'System Config',
        labelKey: 'sharedConsole.navigation.menu.systemConfig',
        icon: 'sliders',
        group: 'system_config',
        priority: 85,
        children: [
            { name: 'settings', label: 'System Settings', labelKey: 'system.navigation.menu.settings', permission: 'view settings', icon: 'settings' },
            { name: 'settings-console-appearance', label: 'Console Appearance', labelKey: 'system.navigation.menu.consoleAppearance', permission: 'manage settings', icon: 'palette' },
            { name: 'languages', label: 'Languages', labelKey: 'system.navigation.menu.languages', permission: 'view settings', icon: 'languages' },
            { name: 'system-notifications', label: 'Notifications', labelKey: 'system.navigation.menu.systemNotifications', permission: 'manage system', icon: 'system-notifications' },
        ],
    },
    {
        label: 'Infrastructure',
        labelKey: 'system.navigation.sections.infrastructure',
        icon: 'settings',
        group: 'infrastructure',
        priority: 80,
        children: [
            { name: 'system', label: 'System Info', labelKey: 'system.navigation.menu.systemInfo', permission: 'view system', role: 'super', priority: 100, icon: 'system' },
            { name: 'backups', label: 'Backups', labelKey: 'system.navigation.menu.backups', permission: 'view backups', role: 'super', priority: 90, icon: 'backups' },
            { name: 'redis', label: 'Redis Cache', labelKey: 'system.navigation.menu.redis', permission: 'manage settings', role: 'super', priority: 80, icon: 'redis' },
            { name: 'scheduled-tasks', label: 'Scheduled Tasks', labelKey: 'system.navigation.menu.scheduledTasks', permission: 'view scheduled tasks', role: 'super', priority: 70, icon: 'scheduled-tasks' },
        ],
    },
    {
        label: 'Identity & Integrations',
        labelKey: 'system.navigation.menu.identityIntegrations',
        icon: 'code',
        group: 'integrations_dev',
        priority: 75,
        children: [
            { name: 'extensions', label: 'Extensions & App Store', labelKey: 'system.navigation.menu.extensions', permission: 'manage settings', role: 'super', priority: 100, icon: 'extensions' },
            { name: 'platform-integrations', label: 'Overview', labelKey: 'system.navigation.menu.integrations', permission: 'manage system', role: 'super', priority: 90, icon: 'code' },
            { name: 'oauth-clients', label: 'OAuth Clients', labelKey: 'system.navigation.menu.oauthClients', permission: 'manage system', role: 'super', priority: 85, icon: 'security' },
            { name: 'webhooks', label: 'Webhooks', labelKey: 'system.navigation.menu.webhooks', permission: 'manage system', role: 'super', priority: 80, icon: 'webhooks' },
        ],
    },
];

export default systemNavigation;
