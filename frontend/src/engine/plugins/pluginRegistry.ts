import { ref, markRaw, type Component } from 'vue';
import slotManifest from './slot-manifest.json';

const knownSlots = new Set(Object.keys(slotManifest as Record<string, unknown>));

export interface RegisteredBlock {
    pluginSlug: string;
    component: Component;
    priority: number;
}

// Global reactive registry state
const registry = ref<Record<string, RegisteredBlock[]>>({});

/**
 * Register a visual frontend component (App Block) to a specific slot name.
 */
export function listKnownSlots(): string[] {
    return [...knownSlots];
}

export function registerAppBlock(
    slotName: string,
    config: {
        pluginSlug: string;
        component: Component;
        priority?: number;
    }
): void {
    if (!slotName) return;
    if (knownSlots.size > 0 && !knownSlots.has(slotName)) {
        console.warn(`[pluginRegistry] Unknown slot "${slotName}" — add to slot-manifest.json`);
    }

    if (!registry.value[slotName]) {
        registry.value[slotName] = [];
    }

    const priority = typeof config.priority === 'number' ? config.priority : 10;

    // Avoid duplicate registration for the same plugin on the same slot
    registry.value[slotName] = registry.value[slotName].filter(
        (block) => block.pluginSlug !== config.pluginSlug
    );

    registry.value[slotName].push({
        pluginSlug: config.pluginSlug,
        component: markRaw(config.component),
        priority,
    });

    // Sort by priority (lower number renders first)
    registry.value[slotName].sort((a, b) => a.priority - b.priority);
}

/**
 * Retrieve all registered App Blocks for a given slot.
 */
export function getAppBlocksForSlot(slotName: string): RegisteredBlock[] {
    if (!slotName) return [];
    return registry.value[slotName] || [];
}

/**
 * Reset all registered blocks (useful for tests or hot-reloading).
 */
export function unregisterAppBlocksForPlugin(pluginSlug: string): void {
    if (!pluginSlug) return;
    for (const slotName of Object.keys(registry.value)) {
        registry.value[slotName] = (registry.value[slotName] || []).filter(
            (block) => block.pluginSlug !== pluginSlug,
        );
    }
}

export function resetAppBlocksRegistry(): void {
    registry.value = {};
}
