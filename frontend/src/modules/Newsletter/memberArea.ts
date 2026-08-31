import type { MemberAreaContribution } from '@/engine/memberArea/types';

const newsletterRouteMeta = {
    public: true,
    requiresMember: true,
    memberExtension: 'newsletter',
} as const;

export const newsletterMemberAreaContribution: MemberAreaContribution = {
    extensionSlug: 'newsletter',
    dependsOn: ['member'],
    navigation: [
        {
            slug: 'newsletter',
            labelKey: 'member.nav.newsletter',
            routeName: 'member.newsletter',
            order: 60,
            capability: 'member.newsletter',
        },
    ],
    widgets: [
        {
            slug: 'newsletter-status',
            slot: 'dashboard',
            order: 30,
            capability: 'member.newsletter',
            component: () => import('@/modules/Member/components/NewsletterStatusWidget.vue'),
        },
    ],
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
