import type { MemberAreaContribution } from '@/engine/memberArea/types';

const newsletterRouteMeta = {
    public: true,
    requiresMember: true,
    memberExtension: 'newsletter',
} as const;

export const newsletterMemberAreaContribution: MemberAreaContribution = {
    extensionSlug: 'newsletter',
    dependsOn: ['member'],
    routes: [
        {
            path: 'newsletter',
            name: 'member.newsletter',
            component: () => import('@/modules/Member/views/Newsletter.vue'),
            meta: {
                ...newsletterRouteMeta,
                memberCapability: 'member.newsletter',
            },
        },
    ],
};
