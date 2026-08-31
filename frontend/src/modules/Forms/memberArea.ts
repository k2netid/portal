import type { MemberAreaContribution } from '@/engine/memberArea/types';

const formsRouteMeta = {
    public: true,
    requiresMember: true,
    memberExtension: 'forms',
} as const;

export const formsMemberAreaContribution: MemberAreaContribution = {
    extensionSlug: 'forms',
    dependsOn: ['member'],
    navigation: [
        {
            slug: 'submissions',
            labelKey: 'member.nav.submissions',
            routeName: 'member.submissions',
            order: 70,
            capability: 'member.submissions',
        },
    ],
    widgets: [
        {
            slug: 'recent-submissions',
            slot: 'dashboard',
            order: 40,
            capability: 'member.submissions',
            component: () => import('@/modules/Member/components/RecentSubmissionsWidget.vue'),
        },
    ],
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
