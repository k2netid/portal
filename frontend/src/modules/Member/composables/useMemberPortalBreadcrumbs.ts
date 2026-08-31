import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';

export type MemberPortalCrumb = {
    label: string;
    to?: { name: string };
};

const ROUTE_LABEL_KEYS: Record<string, string> = {
    'member.dashboard': 'member.portal.nav.dashboard',
    'member.profile': 'member.portal.nav.profile',
    'member.security': 'member.portal.nav.security',
    'member.bookmarks': 'member.nav.bookmarks',
    'member.comments': 'member.nav.comments',
    'member.newsletter': 'member.nav.newsletter',
    'member.submissions': 'member.nav.submissions',
    'member.feature-unavailable': 'member.portal.unavailable.title',
};

export function useMemberPortalBreadcrumbs() {
    const route = useRoute();
    const { t } = useI18n();

    const crumbs = computed((): MemberPortalCrumb[] => {
        const routeName = typeof route.name === 'string' ? route.name : '';
        const accountLabel = t('member.portal.breadcrumb.account', 'Account');
        const items: MemberPortalCrumb[] = [
            {
                label: accountLabel,
                to: { name: 'member.dashboard' },
            },
        ];

        if (!routeName || routeName === 'member.dashboard') {
            items.push({ label: t('member.portal.nav.dashboard', 'Overview') });
            return items;
        }

        const labelKey = ROUTE_LABEL_KEYS[routeName];
        const pageLabel = labelKey
            ? t(labelKey)
            : routeName.replace(/^member\./, '').replace(/-/g, ' ');

        items.push({ label: pageLabel });
        return items;
    });

    const currentPageTitle = computed(() => crumbs.value.at(-1)?.label ?? '');

    return {
        crumbs,
        currentPageTitle,
    };
}
