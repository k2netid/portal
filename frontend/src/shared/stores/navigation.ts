import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { NavItem } from '@/shared/utils/navigation';

export const TOP_LEVEL_NAV_SECTIONS = [
    'studio',
    'insight',
    'audience',
    'users',
    'journal',
    'configuration',
    'infrastructure',
    'integrations',
] as const;

export type NavSectionKey = (typeof TOP_LEVEL_NAV_SECTIONS)[number];

function mergeChildren(existing: NavItem[] = [], incoming: NavItem[] = []): NavItem[] {
    const merged = [...existing];
    const seen = new Set(merged.map((c) => c.name).filter(Boolean));

    for (const child of incoming) {
        if (child.name && seen.has(child.name)) continue;
        merged.push(child);
        if (child.name) seen.add(child.name);
    }
    return merged;
}

function findMergeParent(groupArr: NavItem[], item: NavItem): NavItem | undefined {
    if (item.children && item.children.length > 0 && item.group) {
        return groupArr.find((g) => g.group === item.group);
    }
    if (item.children && item.children.length > 0 && item.labelKey) {
        return groupArr.find((g) => g.labelKey === item.labelKey);
    }
    return undefined;
}

export const useNavigationStore = defineStore('navigation', () => {
    const registry = ref<Record<string, NavItem[]>>({});

    const registerModuleNavigation = (moduleId: string, items: NavItem[]) => {
        registry.value[moduleId] = items;
    };

    const navigationGroups = computed(() => {
        const groups: Record<string, NavItem[]> = {
            studio: [],
            insight: [],
            audience: [],
            users: [],
            journal: [],
            configuration: [],
            infrastructure: [],
            integrations: [],
        };

        Object.entries(registry.value).forEach(([_moduleId, items]) => {
            if (!items) return;
            items.forEach((item) => {
                const targetGroup = (item.context || item.group || 'studio') as string;
                if (!groups[targetGroup]) {
                    groups[targetGroup] = [];
                }
                const groupArr = groups[targetGroup]!;

                const existingParent = findMergeParent(groupArr, item);
                if (existingParent) {
                    existingParent.children = mergeChildren(existingParent.children, item.children);
                    const incomingPriority = item.priority || 0;
                    const existingPriority = existingParent.priority || 0;
                    if (incomingPriority >= existingPriority) {
                        existingParent.priority = incomingPriority;
                        if (item.icon) existingParent.icon = item.icon;
                        if (item.labelKey) existingParent.labelKey = item.labelKey;
                    }
                    return;
                }

                // If item is a single direct navigation entry or an item with children
                groupArr.push({
                    ...item,
                    children: item.children ? [...item.children] : undefined,
                });
            });
        });

        // Sort items inside each group by priority descending
        Object.keys(groups).forEach((key) => {
            const arr = groups[key];
            if (arr) {
                arr.sort((a, b) => (b.priority || 0) - (a.priority || 0));
                arr.forEach((item) => {
                    if (item.children) {
                        item.children.sort((a, b) => (b.priority || 0) - (a.priority || 0));
                    }
                });
            }
        });

        return groups;
    });

    return {
        registry,
        registerModuleNavigation,
        navigationGroups,
    };
});
