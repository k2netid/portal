/**
 * Navigation types for admin sidebar
 */

import type { RouteLocationRaw } from 'vue-router';

export interface NavItem {
    id?: string;
    name?: string;
    to?: string | RouteLocationRaw;
    label?: string;
    labelKey?: string;
    icon?: string;
    permission?: string;
    role?: string | string[];
    context?: 'operations' | 'settings' | 'studio' | 'nexus' | 'command_center' | 'integrations' | 'both' | string;
    type?: 'item' | 'divider';
    children?: NavItem[];
    group?: string;
    priority?: number;
    extension?: string;
    extension_slug?: string;
}
