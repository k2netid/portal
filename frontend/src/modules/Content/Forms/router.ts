import type { RouteRecordRaw } from 'vue-router';

const formsRoutes: RouteRecordRaw[] = [
    {
        path: 'forms',
        name: 'forms',
        component: () => import('@/modules/Content/Forms/views/forms/Index.vue'),
        meta: {
            permission: 'view forms',
            title: 'forms.title',
            breadcrumb: 'forms.navigation.menu.forms',
        },
    },
    {
        path: 'forms/create',
        name: 'forms.create',
        component: () => import('@/modules/Content/Forms/views/forms/Create.vue'),
        meta: {
            permission: 'manage forms',
            title: 'forms.modal.createTitle',
            breadcrumb: 'forms.modal.createTitle',
        },
    },
    {
        path: 'forms/:id/edit',
        name: 'forms.edit',
        component: () => import('@/modules/Content/Forms/views/forms/Edit.vue'),
        meta: {
            permission: 'manage forms',
            title: 'forms.modal.editTitle',
            breadcrumb: 'forms.modal.editTitle',
        },
    },
    {
        path: 'forms/:id/submissions',
        name: 'forms.submissions',
        component: () => import('@/modules/Content/Forms/views/forms/SubmissionsPage.vue'),
        meta: {
            permission: 'view forms',
            title: 'forms.submissions.title',
            breadcrumb: 'forms.submissions.title',
        },
    },
    {
        path: 'forms/:id/analytics',
        name: 'forms.analytics',
        component: () => import('@/modules/Content/Forms/views/forms/AnalyticsPage.vue'),
        meta: {
            permission: 'view forms',
            title: 'forms.submissions.analytics.title',
            breadcrumb: 'forms.submissions.analytics.title',
        },
    },
];

export default formsRoutes;
