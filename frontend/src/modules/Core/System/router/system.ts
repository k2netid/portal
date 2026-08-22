import type { RouteRecordRaw } from 'vue-router';

const systemRoutes: RouteRecordRaw[] = [
    {
        path: 'onboarding',
        name: 'onboarding',
        component: () => import('@/modules/Core/System/views/OnboardingView.vue'),
        meta: { title: 'system.onboarding.title', breadcrumb: 'system.onboarding.title', permission: 'view dashboard' },
    },
    {
        path: 'settings',
        name: 'settings',
        component: () => import('@/modules/Core/System/views/settings/general/Index.vue'),
        meta: {
            permission: 'manage settings',
            title: 'system.settings.title',
            breadcrumb: 'system.navigation.menu.settings',
        },
    },
    {
        path: 'settings/console-appearance',
        name: 'settings-console-appearance',
        component: () => import('@/modules/Core/System/views/settings/console-appearance/Index.vue'),
        meta: { title: 'system.settings.consoleAppearance.title', breadcrumb: 'system.settings.consoleAppearance.title', permission: 'manage settings' },
    },
    {
        path: 'extensions',
        name: 'extensions',
        component: () => import('@/modules/Core/System/views/settings/extensions/Index.vue'),
        meta: { title: 'system.navigation.menu.extensions', breadcrumb: 'system.navigation.menu.extensions', permission: 'manage settings' },
    },
    {
        path: 'cache',
        name: 'cache',
        component: () => import('@/modules/Core/System/views/settings/cache/Index.vue'),
        meta: {
            title: 'system.settings.cache.page.title',
            breadcrumb: 'system.settings.cache.page.title',
            permission: 'manage settings',
        },
    },
    {
        path: 'backups',
        name: 'backups',
        component: () => import('@/modules/Core/System/views/settings/backups/Index.vue'),
        meta: {
            permission: 'manage backups',
            title: 'system.system.backups.title',
            breadcrumb: 'system.navigation.menu.backups',
        },
    },

    {
        path: 'system',
        name: 'system',
        component: () => import('@/modules/Core/System/views/settings/system/Index.vue'),
        meta: {
            permission: 'manage system',
            title: 'system.system.info.title',
            breadcrumb: 'system.navigation.menu.systemInfo',
        },
    },
    {
        path: 'redis',
        name: 'redis',
        component: () => import('@/modules/Core/System/views/settings/system/Redis.vue'),
        meta: { title: 'system.navigation.menu.redis', breadcrumb: 'system.redis.title', permission: 'manage settings' },
    },
    {
        path: 'system/notifications',
        name: 'system-notifications',
        component: () => import('@/modules/Core/System/views/settings/system/NotificationManager.vue'),
        meta: { title: 'system.settings.groups.notifications.title', breadcrumb: 'system.system.notifications.title', permission: 'manage system' },
    },
    {
        path: 'activity-journal',
        name: 'activity-journal',
        component: () => import('@/modules/Core/System/views/pulse/activity/Index.vue'),
        meta: {
            noCache: true,
            title: 'system.activity_journal.title',
            breadcrumb: 'system.navigation.menu.activityJournal',
            permission: 'view activity logs',
        },
    },
    {
        path: 'access-journal',
        name: 'access-journal',
        component: () => import('@/modules/Core/System/views/pulse/access/Index.vue'),
        meta: {
            noCache: true,
            title: 'system.access_journal.title',
            breadcrumb: 'system.navigation.menu.accessJournal',
            permission: 'view access logs',
        },
    },
    {
        path: 'journal-dashboard',
        name: 'journal-dashboard',
        component: () => import('@/modules/Core/System/views/pulse/Index.vue'),
        meta: {
            noCache: true,
            title: 'system.journal_dashboard.title',
            breadcrumb: 'system.navigation.menu.journalDashboard',
            permission: 'view activity logs',
        },
    },
    {
        path: 'notifications',
        name: 'notifications',
        component: () => import('@/modules/Core/System/views/settings/notifications/Index.vue'),
        meta: {
            title: 'system.notifications.title',
            breadcrumb: 'system.notifications.title',
            permission: 'manage settings',
        },
    },
    {
        path: 'scheduled-tasks',
        name: 'scheduled-tasks',
        component: () => import('@/modules/Core/System/views/settings/system/ScheduledTasks.vue'),
        meta: {
            title: 'system.scheduled_tasks.title',
            breadcrumb: 'system.scheduled_tasks.title',
            permission: 'manage scheduled tasks',
        },
    },
    {
        path: 'languages',
        name: 'languages',
        component: () => import('@/modules/Core/System/views/settings/languages/Index.vue'),
        meta: {
            permission: 'manage settings',
            title: 'system.languages.title',
            breadcrumb: 'system.navigation.menu.languages',
        },
    },
    {
        path: 'system-journal',
        name: 'system-journal',
        component: () => import('@/modules/Core/System/views/pulse/system/Index.vue'),
        meta: {
            noCache: true,
            title: 'system.system.logs.title',
            breadcrumb: 'system.navigation.menu.systemJournal',
            permission: 'view system logs',
        },
    },
    {
        path: 'mail',
        name: 'mail',
        component: () => import('@/modules/Core/System/views/mail/Index.vue'),
        meta: {
            title: 'system.mail.title',
            breadcrumb: 'system.navigation.menu.mail',
            permission: 'manage system',
        },
    },
];

export default systemRoutes;
