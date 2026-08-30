import type { RouteRecordRaw } from 'vue-router';

const cmsAiRoutes: RouteRecordRaw[] = [
    {
        path: 'ai',
        name: 'ai-panel',
        component: () => import('@/modules/CmsAi/views/ai/Index.vue'),
        meta: {
            extension: 'cms-ai',
            title: 'ai.title',
            breadcrumb: 'ai.navigation.panel',
            permission: 'manage settings',
            noCache: true,
        },
    },
];

export default cmsAiRoutes;
