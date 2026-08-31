import type { MemberAreaContribution } from '@/engine/memberArea/types';

export const coreMemberAreaContribution: MemberAreaContribution = {
    extensionSlug: 'member',
    navigation: [
        {
            slug: 'dashboard',
            labelKey: 'member.portal.nav.dashboard',
            routeName: 'member.dashboard',
            order: 10,
        },
        {
            slug: 'profile',
            labelKey: 'member.portal.nav.profile',
            routeName: 'member.profile',
            order: 20,
        },
        {
            slug: 'security',
            labelKey: 'member.portal.nav.security',
            routeName: 'member.security',
            order: 30,
        },
    ],
};
