import type { MemberAreaContribution } from '@/engine/memberArea/types';

const publishingRouteMeta = {
    public: true,
    requiresMember: true,
    requiresVerified: true,
    memberExtension: 'publishing',
} as const;

export const publishingMemberAreaContribution: MemberAreaContribution = {
    extensionSlug: 'publishing',
    dependsOn: ['member'],
    navigation: [
        {
            slug: 'bookmarks',
            labelKey: 'member.nav.bookmarks',
            routeName: 'member.bookmarks',
            order: 40,
            requiresVerified: true,
            capability: 'member.bookmarks',
        },
        {
            slug: 'comments',
            labelKey: 'member.nav.comments',
            routeName: 'member.comments',
            order: 50,
            requiresVerified: true,
            capability: 'member.comments',
        },
    ],
    widgets: [
        {
            slug: 'recent-bookmarks',
            slot: 'dashboard',
            order: 10,
            requiresVerified: true,
            capability: 'member.bookmarks',
            component: () => import('@/modules/Member/components/RecentBookmarksWidget.vue'),
        },
        {
            slug: 'recent-comments',
            slot: 'dashboard',
            order: 20,
            requiresVerified: true,
            capability: 'member.comments',
            component: () => import('@/modules/Member/components/RecentCommentsWidget.vue'),
        },
    ],
    routes: [
        {
            path: 'bookmarks',
            name: 'member.bookmarks',
            component: () => import('@/modules/Member/views/Bookmarks.vue'),
            meta: {
                ...publishingRouteMeta,
                memberCapability: 'member.bookmarks',
            },
        },
        {
            path: 'comments',
            name: 'member.comments',
            component: () => import('@/modules/Member/views/Comments.vue'),
            meta: {
                ...publishingRouteMeta,
                memberCapability: 'member.comments',
            },
        },
    ],
};
