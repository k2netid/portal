import type { MemberAreaContribution } from '@/engine/memberArea/types';

const formsRouteMeta = {
    public: true,
    requiresMember: true,
    memberExtension: 'forms',
} as const;

export const formsMemberAreaContribution: MemberAreaContribution = {
    extensionSlug: 'forms',
    dependsOn: ['member'],
    routes: [
        {
            path: 'submissions',
            name: 'member.submissions',
            component: () => import('@/modules/Member/views/Submissions.vue'),
            meta: {
                ...formsRouteMeta,
                memberCapability: 'member.submissions',
            },
        },
    ],
};
