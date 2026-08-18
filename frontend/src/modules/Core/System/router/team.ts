import type { RouteRecordRaw } from 'vue-router';

const teamRoutes: RouteRecordRaw[] = [
    {
        path: 'users',
        name: 'users.index',
        component: () => import('@/modules/Core/System/views/team/users/Index.vue'),
        meta: {
            permission: 'manage users',
            title: 'system.users.title',
            breadcrumb: 'system.navigation.menu.users',
        },
    },
    {
        path: 'users/create',
        name: 'users.create',
        component: () => import('@/modules/Core/System/views/team/users/Create.vue'),
        meta: {
            title: 'system.users.form.titleCreate',
            breadcrumb: 'system.users.form.titleCreate',
            permission: 'manage users',
        },
    },
    {
        path: 'users/:id/edit',
        name: 'users.edit',
        component: () => import('@/modules/Core/System/views/team/users/Edit.vue'),
        meta: {
            title: 'system.users.form.titleEdit',
            breadcrumb: 'system.users.form.titleEdit',
            permission: 'manage users',
        },
    },
    {
        path: 'kyc-reviews',
        name: 'kyc-reviews',
        component: () => import('@/modules/Core/System/views/team/kyc-reviews/Index.vue'),
        meta: {
            permission: 'manage kyc reviews',
            title: 'system.kycReviews.title',
            breadcrumb: 'system.navigation.menu.kycReviews',
        },
    },
    {
        path: 'roles',
        name: 'roles',
        component: () => import('@/modules/Core/System/views/team/roles/Index.vue'),
        meta: {
            permission: 'view roles',
            title: 'system.roles.title',
            breadcrumb: 'system.navigation.menu.roles',
        },
    },
    {
        path: 'roles/create',
        name: 'roles.create',
        component: () => import('@/modules/Core/System/views/team/roles/Index.vue'),
        meta: {
            title: 'system.roles.panel.create.title',
            breadcrumb: 'system.roles.panel.create.title',
            permission: 'view roles',
        },
    },
    {
        path: 'roles/:id/edit',
        name: 'roles.edit',
        component: () => import('@/modules/Core/System/views/team/roles/Index.vue'),
        meta: {
            title: 'system.roles.title',
            breadcrumb: 'system.roles.title',
            permission: 'view roles',
        },
    },
    {
        path: 'profile',
        name: 'profile',
        component: () => import('@/modules/Core/System/views/Profile.vue'),
        meta: {
            title: 'system.profile.title',
            breadcrumb: 'system.profile.title',
        },
    },
];

export default teamRoutes;
