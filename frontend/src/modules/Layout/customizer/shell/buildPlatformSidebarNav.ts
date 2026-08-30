import type { Component } from 'vue';
import type { ThemeSection } from '@/modules/Layout/types/theme';
import platformSidebarGroups from '@/modules/Layout/customizer/platform/sidebar.groups.json';

export type CustomizerSidebarPanel = 'menus' | 'css';

export interface CustomizerNavItem {
    id: string;
    label: string;
    description: string;
    icon: Component | object;
    manifestSections?: ThemeSection[];
    hasBinding: boolean;
    panel?: CustomizerSidebarPanel;
}

interface SidebarItemDef {
    id: string;
    labelKey: string;
    descriptionKey: string;
    icon: string;
    manifestCategories?: string[];
    panel?: CustomizerSidebarPanel;
}

interface SidebarGroupDef {
    id: string;
    labelKey: string;
    items: SidebarItemDef[];
}

export function buildPlatformSidebarGroups(
    t: (key: string) => string,
    findSections: (categories: string[]) => ThemeSection[],
    iconByKey: Record<string, Component>,
): { id: string; label: string; items: CustomizerNavItem[] }[] {
    const groups = platformSidebarGroups.groups as SidebarGroupDef[];

    return groups.map((group) => ({
        id: group.id,
        label: t(group.labelKey),
        items: group.items.map((item) => ({
            id: item.id,
            label: t(item.labelKey),
            description: t(item.descriptionKey),
            icon: iconByKey[item.icon] ?? iconByKey.globe!,
            manifestSections: item.manifestCategories?.length
                ? findSections(item.manifestCategories)
                : undefined,
            hasBinding: false,
            panel: item.panel,
        })),
    }));
}
