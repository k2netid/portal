import type { RouteRecordRaw } from 'vue-router';

const publishingRoutes: RouteRecordRaw[] = [
    {
        path: 'contents',
        name: 'contents.index',
        component: () => import('@/modules/Publishing/views/Index.vue'),
        meta: { extension: 'publishing', permission: 'view content', title: 'publishing.content.list.title', breadcrumb: 'publishing.navigation.menu.studio' },
    },
    {
        path: 'contents/create',
        name: 'contents.create',
        component: () => import('@/modules/Publishing/views/contents/Create.vue'),
        meta: { extension: 'publishing', permission: 'create content', title: 'publishing.content.list.createNew', breadcrumb: 'publishing.content.list.createNew' },
    },
    {
        path: 'contents/:id/edit',
        name: 'contents.edit',
        component: () => import('@/modules/Publishing/views/contents/Edit.vue'),
        meta: { extension: 'publishing', permission: 'edit content', title: 'publishing.content.form.editTitle', breadcrumb: 'publishing.content.form.editTitle' },
    },    {
        path: 'contents/calendar',
        name: 'contents.calendar',
        component: () => import('@/modules/Publishing/views/contents/Calendar.vue'),
        meta: { extension: 'publishing', permission: 'view content', title: 'publishing.content.list.calendar', breadcrumb: 'publishing.content.list.calendar' },
    },
    {
        path: 'contents/:id/revisions',
        name: 'contents.revisions',
        component: () => import('@/modules/Publishing/views/contents/Revisions.vue'),
        meta: { extension: 'publishing', permission: 'view content', title: 'publishing.content.list.revisions', breadcrumb: 'publishing.content.list.revisions' },
    },

    {
        path: 'categories',
        name: 'categories.index',
        redirect: () => ({ name: 'contents.index', query: { tab: 'categories' } }),
    },
    {
        path: 'content-templates',
        name: 'content-templates.index',
        component: () => import('@/modules/Publishing/views/content-templates/Index.vue'),
        meta: { extension: 'publishing', permission: 'manage content templates', title: 'publishing.content_templates.title', breadcrumb: 'publishing.content.list.templates' },
    },
    {
        path: 'content-templates/create',
        name: 'content-templates.create',
        component: () => import('@/modules/Publishing/views/content-templates/Create.vue'),
        meta: { extension: 'publishing', permission: 'manage content templates', title: 'publishing.content_templates.form.createTitle', breadcrumb: 'publishing.content_templates.form.createTitle' },
    },
    {
        path: 'content-templates/:id/edit',
        name: 'content-templates.edit',
        component: () => import('@/modules/Publishing/views/content-templates/Edit.vue'),
        meta: { extension: 'publishing', permission: 'manage content templates', title: 'publishing.content_templates.form.editTitle', breadcrumb: 'publishing.content_templates.form.editTitle' },
    },
    {
        path: 'comments',
        name: 'comments.index',
        component: () => import('@/modules/Publishing/views/comments/Index.vue'),
        meta: { extension: 'publishing', permission: 'view comments', title: 'publishing.comments.list.title', breadcrumb: 'publishing.comments.list.title' },
    },


    {
        path: 'seo',
        name: 'publishing.seo',
        component: () => import('@/modules/Publishing/views/seo/Index.vue'),
        meta: { extension: 'publishing', permission: 'view seo', title: 'publishing.seo.title', breadcrumb: 'publishing.navigation.menu.seo' },
    },
    {
        path: 'publishing/settings',
        name: 'publishing-settings',
        component: () => import('@/modules/Publishing/views/settings/Index.vue'),
        meta: {
            extension: 'publishing',
            permission: 'view settings',
            title: 'publishing.settings.title',
            breadcrumb: 'publishing.navigation.menu.publishingSettings',
        },
    },
];

export default publishingRoutes;
