/**
 * Navigation types for admin sidebar
 */

import type { RouteLocationRaw } from 'vue-router';

export interface NavItem {
    name?: string;
    to?: string | RouteLocationRaw;
    label?: string;
    labelKey?: string;
    icon?: string;
    permission?: string;
    role?: string | string[];
    context?: 'operations' | 'settings' | 'studio' | 'nexus' | 'command_center' | 'both';
    type?: 'item' | 'divider';
    children?: NavItem[];
    group?:
        | 'crm'
        | 'accounting'
        | 'platform'
        | 'studio'
        | 'nexus'
        | 'identity'
        | 'observability'
        | 'infrastructure'
        | 'system_config'
        | 'integrations_dev'
        | 'editorial'
        | 'media'
        | 'design'
        | 'library'
        | 'insights'
        | 'audience';
    priority?: number;
}
