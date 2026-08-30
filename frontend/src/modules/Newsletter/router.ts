import type { RouteRecordRaw } from 'vue-router';

const newsletterRoutes: RouteRecordRaw[] = [
    {
        path: 'newsletter',
        name: 'newsletter',
        component: () => import('@/modules/Newsletter/views/newsletter/Index.vue'),
        meta: {
            permission: 'view newsletter',
            extension: 'newsletter',
            title: 'newsletter.title',
            breadcrumb: 'newsletter.navigation.menu.newsletter',
        },
    },
    {
        path: 'email-templates',
        name: 'email-templates',
        component: () => import('@/modules/Newsletter/views/email-templates/Index.vue'),
        meta: {
            permission: 'manage settings',
            extension: 'newsletter',
            title: 'newsletter.email_templates.list.title',
            breadcrumb: 'newsletter.navigation.menu.emailTemplates',
        },
    },
    {
        path: 'email-templates/create',
        name: 'email-templates.create',
        component: () => import('@/modules/Newsletter/views/email-templates/Create.vue'),
        meta: {
            extension: 'newsletter',
            title: 'newsletter.email_templates.form.createTitle',
            breadcrumb: 'newsletter.email_templates.form.createTitle',
        },
    },
    {
        path: 'email-templates/:id/edit',
        name: 'email-templates.edit',
        component: () => import('@/modules/Newsletter/views/email-templates/Edit.vue'),
        meta: {
            title: 'newsletter.email_templates.form.editTitle',
            breadcrumb: 'newsletter.email_templates.form.editTitle',
            permission: 'manage settings',
            extension: 'newsletter',
        },
    },
];

export default newsletterRoutes;
