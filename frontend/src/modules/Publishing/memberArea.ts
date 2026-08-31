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
