import { computed, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import type { Component } from 'vue';
import {
    Bookmark,
    FileText,
    LayoutDashboard,
    Mail,
    MessageSquare,
    Shield,
    User,
} from 'lucide-vue-next';
import { memberAreaRegistry } from '@/engine/memberArea/MemberAreaRegistry';
import { useMemberStore } from '@/modules/Member/stores/member';
import { useSystemStore } from '@/modules/Core/System/stores/system';

const COLLAPSED_KEY = 'member-portal-sidebar-collapsed';
const SECTIONS_KEY = 'member-portal-sidebar-sections';

export type MemberSidebarNavItem = {
    slug: string;
    labelKey: string;
    routeName: string;
    extensionSlug?: string;
    icon: Component;
};

export type MemberSidebarGroup = {
    id: string;
    labelKey: string;
    items: MemberSidebarNavItem[];
};

const NAV_ICONS: Record<string, Component> = {
    dashboard: LayoutDashboard,
    profile: User,
    security: Shield,
    bookmarks: Bookmark,
    comments: MessageSquare,
    newsletter: Mail,
    submissions: FileText,
};

const GROUP_LABEL_KEYS: Record<string, string> = {
    account: 'member.portal.sidebar.groups.account',
    publishing: 'member.portal.sidebar.groups.activity',
    newsletter: 'member.portal.sidebar.groups.newsletter',
    forms: 'member.portal.sidebar.groups.forms',
};

const GROUP_ORDER: Record<string, number> = {
    account: 10,
    publishing: 20,
    newsletter: 30,
    forms: 40,
};

function readCollapsed(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }
    return window.localStorage.getItem(COLLAPSED_KEY) === '1';
}

function readOpenSections(): Record<string, boolean> {
    if (typeof window === 'undefined') {
        return {};
    }
    try {
        const raw = window.localStorage.getItem(SECTIONS_KEY);
        if (!raw) {
            return {};
        }
        const parsed = JSON.parse(raw) as Record<string, boolean>;
        return typeof parsed === 'object' && parsed !== null ? parsed : {};
    } catch {
        return {};
    }
}

function resolveGroupId(item: { slug: string; extensionSlug?: string }): string {
    if (item.extensionSlug && item.extensionSlug !== 'member') {
        if (item.extensionSlug === 'publishing') {
            return 'publishing';
        }
        if (item.extensionSlug === 'newsletter') {
            return 'newsletter';
        }
        if (item.extensionSlug === 'forms') {
            return 'forms';
        }
        return item.extensionSlug;
    }

    if (item.slug === 'newsletter') {
        return 'newsletter';
    }
    if (item.slug === 'submissions') {
        return 'forms';
    }
    if (item.slug === 'bookmarks' || item.slug === 'comments') {
        return 'publishing';
    }

    return 'account';
}

function iconForSlug(slug: string): Component {
    return NAV_ICONS[slug] ?? User;
}

export function useMemberPortalSidebar() {
    const route = useRoute();
    const memberStore = useMemberStore();
    const systemStore = useSystemStore();

    const collapsed = ref(readCollapsed());
    const openSections = ref<Record<string, boolean>>(readOpenSections());

    const portalContext = computed(() => ({
        activeExtensions: memberStore.portal?.active_extensions
            ?? systemStore.activeExtensions
            ?? [],
        emailVerified: memberStore.member?.email_verified === true,
        capabilities: memberStore.portalCapabilities,
    }));

    const flatNavItems = computed((): MemberSidebarNavItem[] => {
        const portalNav = memberStore.portal?.navigation;
        if (portalNav?.length) {
            return portalNav.map((item) => ({
                slug: item.slug,
                labelKey: item.label_key,
                routeName: item.route,
                extensionSlug: item.extension_slug,
                icon: iconForSlug(item.slug),
            }));
        }

        return memberAreaRegistry.getNavigation(portalContext.value).map((item) => ({
            slug: item.slug,
            labelKey: item.labelKey,
            routeName: item.routeName,
            extensionSlug: item.extensionSlug,
            icon: iconForSlug(item.slug),
        }));
    });

    const navGroups = computed((): MemberSidebarGroup[] => {
        const buckets = new Map<string, MemberSidebarNavItem[]>();

        for (const item of flatNavItems.value) {
            const groupId = resolveGroupId(item);
            const list = buckets.get(groupId) ?? [];
            list.push(item);
            buckets.set(groupId, list);
        }

        return [...buckets.entries()]
            .sort(([a], [b]) => (GROUP_ORDER[a] ?? 99) - (GROUP_ORDER[b] ?? 99))
            .map(([id, items]) => ({
                id,
                labelKey: GROUP_LABEL_KEYS[id] ?? 'member.portal.menuLabel',
                items,
            }));
    });

    const isActive = (routeName: string): boolean => route.name === routeName;

    const groupHasActiveRoute = (group: MemberSidebarGroup): boolean =>
        group.items.some((item) => isActive(item.routeName));

    const isSectionOpen = (groupId: string): boolean => {
        if (collapsed.value) {
            return true;
        }
        if (openSections.value[groupId] !== undefined) {
            return openSections.value[groupId] === true;
        }
        const group = navGroups.value.find((entry) => entry.id === groupId);
        return group ? groupHasActiveRoute(group) : true;
    };

    const toggleSection = (groupId: string): void => {
        if (collapsed.value) {
            return;
        }
        openSections.value = {
            ...openSections.value,
            [groupId]: !isSectionOpen(groupId),
        };
        if (typeof window !== 'undefined') {
            window.localStorage.setItem(SECTIONS_KEY, JSON.stringify(openSections.value));
        }
    };

    const toggleCollapsed = (): void => {
        collapsed.value = !collapsed.value;
        if (typeof window !== 'undefined') {
            window.localStorage.setItem(COLLAPSED_KEY, collapsed.value ? '1' : '0');
        }
    };

    watch(
        () => route.name,
        () => {
            for (const group of navGroups.value) {
                if (groupHasActiveRoute(group)) {
                    openSections.value = {
                        ...openSections.value,
                        [group.id]: true,
                    };
                }
            }
        },
        { immediate: true },
    );

    return {
        collapsed,
        navGroups,
        isActive,
        isSectionOpen,
        toggleSection,
        toggleCollapsed,
    };
}
