import type { RouteRecordRaw } from 'vue-router';

const developerRoutes: RouteRecordRaw[] = [
    {
        path: 'plugins',
        name: 'plugins',
        redirect: { name: 'extensions' },
    },
    {
        path: 'oauth-clients',
        name: 'oauth-clients',
        component: () => import('@/modules/Core/System/views/dev/oauth-clients/Index.vue'),
        meta: {
            permission: 'manage system',
            title: 'system.oauth.title',
            breadcrumb: 'system.oauth.title',
        },
    },
    {
        path: 'oauth/consent',
        name: 'oauth-consent',
        component: () => import('@/modules/Core/System/views/dev/oauth-clients/AuthorizeConsent.vue'),
        meta: {
            title: 'system.oauth.consent.title',
            breadcrumb: 'system.oauth.consent.title',
        },
    },
    {
        path: 'webhooks',
        name: 'webhooks',
        component: () => import('@/modules/Core/System/views/dev/webhooks/Index.vue'),
        meta: {
            permission: 'manage system',
            title: 'system.webhooks.title',
            breadcrumb: 'system.webhooks.title',
        },
    },
    {
        path: 'integrations',
        name: 'platform-integrations',
        component: () => import('@/modules/Core/System/views/dev/integrations/Index.vue'),
        meta: {
            permission: 'manage system',
            title: 'system.integrations.title',
            breadcrumb: 'system.integrations.title',
        },
    },
];

export default developerRoutes;
