import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { NavItem } from '@/shared/utils/navigation';

/** Sidebar parents with the same `group` are merged into one accordion. */
const MERGE_BY_GROUP = new Set<NonNullable<NavItem['group']>>([
    'crm',
    'accounting',
    'platform',
    'studio',
    'editorial',
    'insight',
    'library',
    'audience',
    'nexus',
    'identity',
    'communications',
    'observability',
    'infrastructure',
    'system_config',
    'integrations_dev',
]);

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
    if (item.group && MERGE_BY_GROUP.has(item.group)) {
        return groupArr.find((g) => g.group === item.group);
    }
    if (item.children && item.children.length > 0 && item.labelKey) {
        return groupArr.find((g) => g.labelKey === item.labelKey);
    }
    return undefined;
}

function mergeIntoParent(existingParent: NavItem, item: NavItem): void {
    if (item.children && item.children.length > 0) {
        existingParent.children = mergeChildren(existingParent.children, item.children);
    } else if (item.name || item.to || item.label) {
        existingParent.children = mergeChildren(existingParent.children, [item]);
    }

    const incomingPriority = item.priority || 0;
    const existingPriority = existingParent.priority || 0;
    if (incomingPriority >= existingPriority) {
        existingParent.priority = incomingPriority;
        if (item.icon) existingParent.icon = item.icon;
        if (item.labelKey) existingParent.labelKey = item.labelKey;
        if (item.label) existingParent.label = item.label;
    }
}

function dedupeDatabaseMenus(items: NavItem[]): NavItem[] {
    const byGroup = new Map<string, NavItem>();

    for (const item of items) {
        const key = item.group || item.labelKey || item.label || item.name || item.id || '';
        if (!key) {
            byGroup.set(`__orphan_${byGroup.size}`, item);
            continue;
        }

        const existing = byGroup.get(key);
        if (!existing) {
            byGroup.set(key, {
                ...item,
                children: item.children ? [...item.children] : undefined,
            });
            continue;
        }

        existing.children = mergeChildren(existing.children, item.children);
        if ((item.priority || 0) > (existing.priority || 0)) {
            existing.priority = item.priority;
            if (item.icon) existing.icon = item.icon;
            if (item.labelKey) existing.labelKey = item.labelKey;
            if (item.label) existing.label = item.label;
        }
    }

    return Array.from(byGroup.values()).sort((a, b) => (b.priority || 0) - (a.priority || 0));
}

export const useNavigationStore = defineStore('navigation', () => {
    const registry = ref<Record<string, NavItem[]>>({});
    const dbMenuRegistry = ref<NavItem[] | null>(null);
    const menusReady = ref(false);

    const registerModuleNavigation = (moduleId: string, items: NavItem[]) => {
        registry.value[moduleId] = items;
    };

    const markMenusReady = () => {
        menusReady.value = true;
    };

    const setDatabaseMenus = (menus: Array<{
        id?: string;
        is_visible?: boolean;
        route_name?: string;
        url?: string;
        name: string;
        label_key?: string;
        icon?: string;
        group_slug?: string;
        order?: number;
        permission?: string;
        role?: string;
        extension_slug?: string;
        badge_text?: string;
        badge_variant?: string;
        children?: Array<{
            id?: string;
            is_visible?: boolean;
            route_name?: string;
            url?: string;
            name: string;
            label_key?: string;
            icon?: string;
            permission?: string;
            role?: string;
            extension_slug?: string;
            badge_text?: string;
            badge_variant?: string;
            order?: number;
        }>;
    }>) => {
        if (!Array.isArray(menus) || menus.length === 0) {
            dbMenuRegistry.value = null;
            menusReady.value = true;
            return;
        }

        const items: NavItem[] = menus
            .filter((m) => m.is_visible !== false)
            .map((m) => {
                const isGroupHeader = Array.isArray(m.children) && m.children.length > 0;
                return {
                    id: m.id,
                    name: !isGroupHeader && m.route_name ? m.route_name : undefined,
                    to: !isGroupHeader && m.url ? m.url : (!isGroupHeader && m.route_name ? { name: m.route_name } : undefined),
                    label: m.name,
                    labelKey: m.label_key || undefined,
                    icon: m.icon || 'folder',
                    group: m.group_slug,
                    priority: 100 - (m.order || 0),
                    permission: m.permission || undefined,
                    role: m.role || undefined,
                    extension: m.extension_slug || undefined,
                    children: isGroupHeader
                        ? m.children!
                              .filter((c) => c.is_visible !== false)
                              .map((c) => ({
                                  id: c.id,
                                  name: c.route_name || undefined,
                                  to: c.url ? c.url : (c.route_name ? { name: c.route_name } : undefined),
                                  label: c.name,
                                  labelKey: c.label_key || undefined,
                                  icon: c.icon || 'circle',
                                  permission: c.permission || undefined,
                                  role: c.role || undefined,
                                  extension: c.extension_slug || undefined,
                                  badge_text: c.badge_text || undefined,
                                  badge_variant: c.badge_variant || undefined,
                                  priority: 100 - (c.order || 0),
                              }))
                        : undefined,
                };
            });

        dbMenuRegistry.value = dedupeDatabaseMenus(items);
        menusReady.value = true;
    };

    const navigationItems = computed<NavItem[]>(() => {
        if (dbMenuRegistry.value && dbMenuRegistry.value.length > 0) {
            return dbMenuRegistry.value;
        }

        const list: NavItem[] = [];

        Object.entries(registry.value).forEach(([_moduleId, items]) => {
            if (!items) return;
            items.forEach((item) => {
                const existingParent = findMergeParent(list, item);
                if (existingParent) {
                    mergeIntoParent(existingParent, item);
                    return;
                }

                list.push({
                    ...item,
                    children: item.children ? [...item.children] : undefined,
                });
            });
        });

        list.sort((a, b) => (b.priority || 0) - (a.priority || 0));
        list.forEach((item) => {
            if (item.children) {
                item.children.sort((a, b) => (b.priority || 0) - (a.priority || 0));
            }
        });

        return list;
    });

    const navigationGroups = computed(() => {
        const groups: Record<string, NavItem[]> = {
            operations: [],
            settings: [],
        };

        Object.entries(registry.value).forEach(([_moduleId, items]) => {
            if (!items) return;
            items.forEach((item) => {
                const targetGroup = item.context || 'operations';
                if (!groups[targetGroup]) {
                    groups[targetGroup] = [];
                }
                const groupArr = groups[targetGroup]!;

                const existingParent = findMergeParent(groupArr, item);
                if (existingParent) {
                    mergeIntoParent(existingParent, item);
                    return;
                }

                groupArr.push({
                    ...item,
                    children: item.children ? [...item.children] : undefined,
                });
            });
        });

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
        dbMenuRegistry,
        menusReady,
        registerModuleNavigation,
        setDatabaseMenus,
        markMenusReady,
        navigationItems,
        navigationGroups,
    };
});
