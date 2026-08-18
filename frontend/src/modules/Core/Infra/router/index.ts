import type { RouteRecordRaw } from 'vue-router';

const infraRoutes: RouteRecordRaw[] = [
    {
        path: 'cck',
        name: 'cck-index',
        component: () => import('@/modules/Core/Infra/views/cck/Index.vue'),
        meta: {
            permission: 'manage settings',
            title: 'infra.cck.title',
            breadcrumb: 'infra.cck.title',
        },
    },
    {
        path: 'cck/new',
        name: 'cck-create',
        component: () => import('@/modules/Core/Infra/views/cck/Edit.vue'),
        meta: {
            permission: 'manage settings',
            title: 'infra.cck.newType',
            breadcrumb: 'infra.cck.newType',
        },
    },
    {
        path: 'cck/:id',
        name: 'cck-edit',
        component: () => import('@/modules/Core/Infra/views/cck/Edit.vue'),
        meta: {
            permission: 'manage settings',
            title: 'infra.cck.editType',
            breadcrumb: 'infra.cck.editType',
        },
    },
    {
        path: 'dynamic/:slug/records',
        name: 'dynamic-records-index',
        component: () => import('@/modules/Core/Infra/views/dynamic/Index.vue'),
        meta: {
            permission: 'manage settings',
            title: 'infra.cck.title',
            breadcrumb: 'infra.cck.title',
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
