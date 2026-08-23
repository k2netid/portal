import { logger } from '@/shared/utils/logger';
import { shallowRef, type Component } from 'vue';
import type { MenuItemDefinition, MenuItem, MenuItemSetting } from '@/modules/Layout/types/menu';

/**
 * MenuItemRegistry * Registry for menu item types.
 */
class MenuItemRegistry {
    items = shallowRef(new Map<string, MenuItemDefinition>());

    constructor() {
    }

    /**
     * Register a new item type definition
     */
    register(definition: MenuItemDefinition) {
        if (!definition.name) {
            logger.error('Menu item definition must have a name');
            return;
        }
        this.items.value.set(definition.name, definition);
    }

    /**
     * Register multiple definitions at once
     */
    registerAll(definitions: MenuItemDefinition[]) {
        definitions.forEach(def => this.register(def));
    }

    /**
     * Get an item type definition by name
     */
    get(name: string): MenuItemDefinition | undefined {
        return this.items.value.get(name);
    }

    /**
     * Get all registered item types
     */
    getAll(): MenuItemDefinition[] {
        return Array.from(this.items.value.values());
    }

    /**
     * Get item types grouped by category
     */
    getGrouped(): Record<string, MenuItemDefinition[]> {
        const grouped: Record<string, MenuItemDefinition[]> = {};
        this.getAll().forEach(item => {
            const category = item.category || 'other';
            if (!grouped[category]) {
                grouped[category] = [];
            }
            grouped[category].push(item);
        });
        return grouped;
    }

    /**
     * Create a new item instance from a type
     */
    createInstance(type: string, overrides: Partial<MenuItem> = {}): MenuItem | null {
        const definition = this.get(type);
        if (!definition) {
            logger.warning(`Unknown menu item type: ${type}`);
            return null;
        }

        const defaultValues: Record<string, unknown> = {};
        if (definition.settings) {
            definition.settings.forEach(setting => {
                if (setting.default !== undefined) {
                    defaultValues[setting.key] = setting.default;
                }
            });
        }

        return {
            id: null,
            _temp_id: 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
            type: definition.name,
            title: definition.defaultTitle || 'New Item',
            children: [],
            is_active: 1,
            ...defaultValues,
            ...overrides
        };
    }

    /**
     * Get the settings schema for an item type
     */
    getSettingsSchema(type: string): MenuItemSetting[] {
        const definition = this.get(type);
        return definition?.settings || [];
    }

    /**
     * Get icon component for a type
     */
    getIcon(type: string): Component | string | undefined {
        const definition = this.get(type);
        return definition?.icon;
    }

    /**
     * Get color for a type
     */
    getColor(type: string): string {
        const definition = this.get(type);
        return definition?.color || 'gray';
    }
}

// Export singleton instance
export const menuItemRegistry = new MenuItemRegistry();
