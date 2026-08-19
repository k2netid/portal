/**
 * Navigation types for admin sidebar
 */

import type { RouteLocationRaw } from 'vue-router';

export type NavGroupKey =
    | 'studio'
    | 'insight'
    | 'audience'
    | 'users'
    | 'journal'
    | 'configuration'
    | 'infrastructure'
    | 'integrations'
    | (string & {});

export interface NavItem {
    name?: string;
    to?: string | RouteLocationRaw;
    label?: string;
    labelKey?: string;
    icon?: string;
    permission?: string;
    role?: string | string[];
    context?: NavGroupKey;
    type?: 'item' | 'divider';
    children?: NavItem[];
    group?: NavGroupKey;
    priority?: number;
}
