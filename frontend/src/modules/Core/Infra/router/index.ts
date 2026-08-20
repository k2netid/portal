import type { RouteRecordRaw } from 'vue-router';

const infraRoutes: RouteRecordRaw[] = [
    {
        path: 'models',
        name: 'model-index',
        component: () => import('@/modules/Core/Infra/views/models/Index.vue'),
        meta: {
            permission: 'manage settings',
            title: 'infra.models.title',
            breadcrumb: 'infra.models.title',
        },
    },
    {
        path: 'models/new',
        name: 'model-create',
        component: () => import('@/modules/Core/Infra/views/models/Edit.vue'),
        meta: {
            permission: 'manage settings',
            title: 'infra.models.newType',
            breadcrumb: 'infra.models.newType',
        },
    },
    {
        path: 'models/:id',
        name: 'model-edit',
        component: () => import('@/modules/Core/Infra/views/models/Edit.vue'),
        meta: {
            permission: 'manage settings',
            title: 'infra.models.editType',
            breadcrumb: 'infra.models.editType',
        },
    },
    {
        path: 'dynamic/:slug/records',
        name: 'dynamic-records-index',
        component: () => import('@/modules/Core/Infra/views/dynamic/Index.vue'),
        meta: {
            permission: 'manage settings',
            title: 'infra.models.title',
            breadcrumb: 'infra.models.title',
        },
    },
    {
        path: 'dynamic/:slug/records/new',
        name: 'dynamic-records-create',
        component: () => import('@/modules/Core/Infra/views/dynamic/Edit.vue'),
        meta: {
            permission: 'manage settings',
            title: 'infra.dynamic.record.newTitle',
            breadcrumb: 'infra.dynamic.record.newTitle',
        },
    },
    {
        path: 'dynamic/:slug/records/:recordId',
        name: 'dynamic-records-edit',
        component: () => import('@/modules/Core/Infra/views/dynamic/Edit.vue'),
        meta: {
            permission: 'manage settings',
            title: 'infra.dynamic.record.editTitle',
            breadcrumb: 'infra.dynamic.record.editTitle',
        },
    },
];

export default infraRoutes;
