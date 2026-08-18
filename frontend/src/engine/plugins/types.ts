import type { Component } from 'vue';

export interface ThemeBlockRegistration {
    pluginSlug: string;
    component: Component;
    priority?: number;
}

export type RegisterThemeBlocksFn = (register: (slotName: string, block: ThemeBlockRegistration) => void) => void;

export interface PluginBlocksManifestEntry {
    slug: string;
    priority: number;
    slots: string[];
    /** HTTPS blocks.js URL when FEATURE_REMOTE_PLUGIN_BLOCKS + allowlisted host */
    blocks_url?: string;
}

export interface PluginBlocksApiResponse {
    plugins: PluginBlocksManifestEntry[];
    slots: string[];
    /** HTTPS blocks.js URL when FEATURE_REMOTE_PLUGIN_BLOCKS + allowlisted host */
    blocks_url?: string;
}
