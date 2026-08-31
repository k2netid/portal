import type { RouteRecordRaw } from 'vue-router';

export interface MemberAreaNavItem {
    slug: string;
    labelKey: string;
    routeName: string;
    order?: number;
    requiresVerified?: boolean;
    capability?: string;
    extensionSlug?: string;
}

export interface MemberAreaWidget {
    slug: string;
    slot: 'dashboard';
    order?: number;
    requiresVerified?: boolean;
    capability?: string;
    extensionSlug?: string;
    component: () => Promise<unknown>;
}

export interface MemberAreaContribution {
    extensionSlug: string;
    dependsOn?: string[];
    routes?: RouteRecordRaw[];
    navigation?: MemberAreaNavItem[];
    widgets?: MemberAreaWidget[];
}

export interface MemberPortalContext {
    activeExtensions: string[];
    emailVerified: boolean;
    capabilities?: string[];
}
