import { logger } from '@/shared/utils/logger';
/**
 * Module Registry
 * Manages module definitions and their Vue components
 */
import type { BlockInstance, ModuleSettings, ModuleDefinition } from '@/types/builder'
import type { Component } from 'vue'
import ValidationService from './ValidationService';

class ModuleRegistry {
    private modules: Map<string, ModuleDefinition>;
    private components: Map<string, Component>;

    constructor() {
        this.modules = new Map();
        this.components = new Map();
    }

    /**
     * Resolve alias names to canonical module names
     */
    resolveAlias(name: string): string {
        const aliases: Record<string, string> = {
            'richtext': 'text',
            'rich_text': 'text',
            'editor': 'text',
            'wysiwyg': 'text',
            'paragraph': 'text',
            'html': 'text',
            'heading_block': 'heading',
            'code_block': 'code',
            'form': 'form_picker',
            'fullwidth_section': 'section'
        };
        return aliases[name] || name;
    }

    /**
     * Register a module definition
     * @param definition - Module definition object
     */
    register(definition: ModuleDefinition): void {
        if (!definition.name) {
            logger.error('Module definition must have a name');
            return;
        }
        this.modules.set(definition.name, definition);
    }

    /**
     * Register a Vue component for a module
     * @param name - Module name
     * @param component - Vue component
     */
    registerComponent(name: string, component: Component): void {
        this.components.set(name, component);
    }

    /**
     * Get a module definition by name
     * @param name - Module name
     * @returns ModuleDefinition | undefined
     */
    get(name: string): ModuleDefinition | undefined {
        const canonical = this.resolveAlias(name);
        return this.modules.get(canonical);
    }

    /**
     * Get a Vue component by module name
     * @param name - Module name
     * @returns Component | undefined
     */
    getComponent(name: string): Component | undefined {
        const canonical = this.resolveAlias(name);
        return this.components.get(canonical) || this.components.get(name);
    }

    /**
     * Get all registered modules
     * @returns Array<ModuleDefinition>
     */
    getAll(): ModuleDefinition[] {
        return Array.from(this.modules.values());
    }

    /**
     * Get modules by category
     * @param category - Category ID
     * @returns Array<ModuleDefinition>
     */
    getByCategory(category: string): ModuleDefinition[] {
        return this.getAll().filter(m => m.category === category);
    }

    /**
     * Get structure modules (Section, Row, Column)
     * @returns Array<ModuleDefinition>
     */
    getStructureModules(): ModuleDefinition[] {
        return this.getByCategory('structure');
    }

    /**
     * Get content modules (non-structure)
     * @returns Array<ModuleDefinition>
     */
    getContentModules(): ModuleDefinition[] {
        return this.getAll().filter(m => m.category !== 'structure');
    }

    /**
     * Check if a module exists
     * @param name - Module name
     * @returns boolean
     */
    has(name: string): boolean {
        const canonical = this.resolveAlias(name);
        return this.modules.has(canonical);
    }

    /**
     * Create a new module instance with default settings
     * @param name - Module name
     * @param overrides - Settings overrides
     * @returns BlockInstance | null
     */
    createInstance(name: string, overrides: ModuleSettings = {}): BlockInstance | null {
        const canonical = this.resolveAlias(name);
        const definition = this.get(canonical);
        if (!definition) {
            logger.error(`Module "${name}" not found in registry`);
            return null;
        }

        const initialSettings = {
            ...JSON.parse(JSON.stringify(definition.defaults || {})),
            ...overrides
        };

        const validation = ValidationService.validate(canonical, initialSettings);
        if (!validation.success) {
            logger.warning(`[ModuleRegistry] Created instance of ${canonical} has invalid settings:`, validation.errors);
        }

        const instance: BlockInstance = {
            id: this.generateId(),
            type: canonical,
            settings: validation.data || initialSettings // Use validated/transformed settings if available
        };

        if (definition.children && Array.isArray(definition.children)) {
            instance.children = [];
        }

        return instance;
    }

    /**
     * Generate unique ID
     * @returns string
     */
    generateId(): string {
        return `module-${Date.now()}-${Math.random().toString(36).substring(2, 11)}`;
    }
}

// Singleton instance
export default new ModuleRegistry();
