import type { RouteRecordRaw } from 'vue-router';

const aiRoutes: RouteRecordRaw[] = [
    {
        path: 'ai',
        name: 'ai-panel',
        component: () => import('@/modules/Intelligence/Ai/views/ai/Index.vue'),
        meta: { title: 'ai.title', breadcrumb: 'ai.navigation.panel', permission: 'manage settings', noCache: true },
    },
];

export default aiRoutes;
