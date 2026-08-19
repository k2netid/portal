import type { NavItem } from '@/shared/utils/navigation';

export const systemNavigation: NavItem[] = [
    // --- Users ---
    { name: 'users.index', label: 'Users', labelKey: 'system.navigation.menu.users', permission: 'view users', icon: 'users', group: 'users', context: 'users', priority: 100 },
    { name: 'roles', label: 'Roles & Permissions', labelKey: 'system.navigation.menu.roles', permission: 'view roles', icon: 'roles', group: 'users', context: 'users', priority: 90 },
    { name: 'kyc-reviews', label: 'KYC Reviews', labelKey: 'system.navigation.menu.kycReviews', permission: 'manage kyc reviews', icon: 'user-check', group: 'users', context: 'users', priority: 80 },

    // --- Journal ---
    { name: 'journal-dashboard', label: 'Journal Dashboard', labelKey: 'system.navigation.menu.journalDashboard', permission: 'view logs', icon: 'journal-dashboard', role: 'super', group: 'journal', context: 'journal', priority: 100 },
    { name: 'activity-journal', label: 'Activity Journal', labelKey: 'system.navigation.menu.activityJournal', permission: 'view activity logs', icon: 'activity-journal', role: 'super', group: 'journal', context: 'journal', priority: 90 },
    { name: 'system-journal', label: 'System Journal', labelKey: 'system.navigation.menu.systemJournal', permission: 'view system', icon: 'system-journal', role: 'super', group: 'journal', context: 'journal', priority: 70 },
    { name: 'access-journal', label: 'Access History', labelKey: 'system.navigation.menu.accessJournal', permission: 'view users', icon: 'access-journal', role: 'super', group: 'journal', context: 'journal', priority: 60 },

    // --- Configuration ---
    { name: 'settings', label: 'System Settings', labelKey: 'system.navigation.menu.settings', permission: 'view settings', icon: 'settings', group: 'configuration', context: 'configuration', priority: 100 },
    { name: 'settings-console-appearance', label: 'Console Appearance', labelKey: 'system.navigation.menu.consoleAppearance', permission: 'manage settings', icon: 'palette', group: 'configuration', context: 'configuration', priority: 90 },
    { name: 'languages', label: 'Languages', labelKey: 'system.navigation.menu.languages', permission: 'view settings', icon: 'languages', group: 'configuration', context: 'configuration', priority: 80 },
    { name: 'system-notifications', label: 'Notifications', labelKey: 'system.navigation.menu.systemNotifications', permission: 'manage system', icon: 'system-notifications', group: 'configuration', context: 'configuration', priority: 70 },

    // --- Infrastructure ---
    { name: 'system', label: 'System Info', labelKey: 'system.navigation.menu.systemInfo', permission: 'view system', role: 'super', icon: 'system', group: 'infrastructure', context: 'infrastructure', priority: 100 },
    { name: 'backups', label: 'Backups', labelKey: 'system.navigation.menu.backups', permission: 'view backups', role: 'super', icon: 'backups', group: 'infrastructure', context: 'infrastructure', priority: 90 },
    { name: 'redis', label: 'Redis Cache', labelKey: 'system.navigation.menu.redis', permission: 'manage settings', role: 'super', icon: 'redis', group: 'infrastructure', context: 'infrastructure', priority: 80 },
    { name: 'scheduled-tasks', label: 'Scheduled Tasks', labelKey: 'system.navigation.menu.scheduledTasks', permission: 'view scheduled tasks', role: 'super', icon: 'scheduled-tasks', group: 'infrastructure', context: 'infrastructure', priority: 70 },

    // --- Integrations ---
    { name: 'extensions', label: 'Extensions & App Store', labelKey: 'system.navigation.menu.extensions', permission: 'manage settings', role: 'super', icon: 'extensions', group: 'integrations', context: 'integrations', priority: 95 },
    { name: 'plugins', label: 'Plugins', labelKey: 'system.navigation.menu.plugins', permission: 'view plugins', icon: 'plugins', group: 'integrations', context: 'integrations', priority: 90 },
    { name: 'platform-integrations', label: 'Overview', labelKey: 'system.navigation.menu.integrations', permission: 'manage system', role: 'super', icon: 'code', group: 'integrations', context: 'integrations', priority: 88 },
    { name: 'oauth-clients', label: 'OAuth Clients', labelKey: 'system.navigation.menu.oauthClients', permission: 'manage system', role: 'super', icon: 'extensions', group: 'integrations', context: 'integrations', priority: 85 },
    { name: 'webhooks', label: 'Webhooks', labelKey: 'system.navigation.menu.webhooks', permission: 'manage system', role: 'super', icon: 'code', group: 'integrations', context: 'integrations', priority: 82 },
];

export default systemNavigation;
