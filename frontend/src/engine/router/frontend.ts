import type { RouteRecordRaw } from 'vue-router'

// Theme Page Resolver
const ThemePageResolver = () => import('@/modules/Content/Layout/components/themes/ThemePageResolver.vue')

// Frontend theme routes
const frontendRoutes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'frontend',
        component: () => import('@/modules/Content/Layout/layouts/FrontendLayout.vue'),
        meta: { public: true },
        children: [
            {
                path: '',
                name: 'home',
                component: ThemePageResolver,
                props: { page: 'Home' },
                meta: {
                    title: 'publishing.frontend.routes.home',
                    description: 'publishing.frontend.routes.homeDescription',
                }
            },
            {
                path: 'blog',
                name: 'blog',
                component: ThemePageResolver,
                props: { page: 'Blog' },
                meta: {
                    title: 'publishing.frontend.routes.blog',
                    description: 'publishing.frontend.routes.blogDescription',
                }
            },
            {
                path: 'blog/:slug',
                name: 'post',
                component: ThemePageResolver,
                props: { page: 'Post' },
                meta: {
                    title: 'publishing.frontend.routes.post',
                }
            },
            {
                path: 'about',
                name: 'about',
                component: ThemePageResolver,
                props: { page: 'About' },
                meta: {
                    title: 'publishing.frontend.routes.about',
                }
            },
            {
                path: 'contact',
                name: 'contact',
                component: ThemePageResolver,
                props: { page: 'Contact' },
                meta: {
                    title: 'publishing.frontend.routes.contact',
                }
            },
            {
                path: 'search',
                name: 'search',
                component: ThemePageResolver,
                props: { page: 'Search' },
                meta: {
                    title: 'publishing.frontend.routes.search',
                }
            },
            {
                path: 'terms',
                name: 'terms',
                component: () => import('@/modules/Core/System/views/legal/Terms.vue'),
                meta: {
                    title: 'publishing.frontend.routes.terms',
                }
            },
            {
                path: 'privacy',
                name: 'privacy',
                component: () => import('@/modules/Core/System/views/legal/Privacy.vue'),
                meta: {
                    title: 'publishing.frontend.routes.privacy',
                }
            },
            {
                path: 'pricing',
                name: 'pricing',
                component: ThemePageResolver,
                props: { page: 'Pricing' },
                meta: { title: 'publishing.frontend.routes.pricing' },
            },
            {
                path: 'solusi',
                name: 'solutions',
                component: ThemePageResolver,
                props: { page: 'Solusi' },
                meta: { title: 'publishing.frontend.routes.solutions' }
            },
            {
                path: 'tim',
                name: 'team',
                component: ThemePageResolver,
                props: { page: 'Tim' },
                meta: { title: 'publishing.frontend.routes.team' }
            },
            {
                path: 'careers',
                name: 'careers',
                component: ThemePageResolver,
                props: { page: 'CareerCenter' },
                meta: { title: 'publishing.frontend.routes.careers' }
            },
            {
                path: 'highlights',
                name: 'highlights',
                component: ThemePageResolver,
                props: { page: 'Achievement' },
                meta: { title: 'publishing.frontend.routes.highlights' }
            },
            {
                path: 'member/profile',
                name: 'member-profile',
                component: () => import('@/modules/Core/System/views/Profile.vue'),
                meta: {
                    requiresAuth: true,
                    title: 'system.profile.title',
                },
            },
            {
                path: 'member',
                redirect: { name: 'member-profile' },
            },

            // Dynamic content route (must be last in children)
            {
                path: ':slug',
                name: 'page',
                component: ThemePageResolver,
                props: { page: 'Page' },
            },
        ]
    },
]

export default frontendRoutes
