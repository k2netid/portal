import type { RouteRecordRaw } from 'vue-router';
import type { NavItem } from '@/shared/utils/navigation';
import type { Component } from 'vue';

export interface AuthPermissionGate {
    hasPermission: (permission: string) => boolean;
}

export interface DashboardConfig {
    id: string;
    priority: number;
    condition: (user: unknown, authStore: AuthPermissionGate) => boolean;
    component: Component | (() => Promise<Component>);
    routeName?: string;
}

/** Console feature module contract (routes, nav, dashboards). Not related to the Janari public theme. */
export interface AppModule {
    id: string;
    name: string;
    /** Registry slug when this AppModule is an optional first-party extension (=== manifest.slug). */
    extensionSlug?: string;
    routes?: RouteRecordRaw[];
    navigation?: NavItem[];
    dashboards?: DashboardConfig[];
    initialize?: () => Promise<void>;
}
