import type { MemberAreaContribution } from '@/engine/memberArea/types';

export const billingMemberAreaContribution: MemberAreaContribution = {
    extensionSlug: 'billing',
    dependsOn: ['member'],
    navigation: [
        {
            slug: 'billing',
            labelKey: 'member.billing.nav',
            routeName: 'member.billing',
            order: 15,
        },
    ],
    routes: [
        {
            path: 'billing',
            name: 'member.billing',
            component: () => import('@/modules/Billing/views/Billing.vue'),
            meta: {
                public: true,
                requiresMember: true,
                memberShell: true,
                title: 'member.billing.title',
            },
        },
    ],
};
