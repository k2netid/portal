import { logger } from '@/utils/logger';
import { shallowRef, type Ref, type Component } from 'vue';
import blockDefinitions from './definitions';
import type { BlockDefinition } from '@/types/builder';

class BlockRegistry {
    private blocks: Ref<Map<string, BlockDefinition>>;

    constructor() {
        this.blocks = shallowRef(new Map<string, BlockDefinition>());
        this.registerAll();
    }

    private registerAll(): void {
        (blockDefinitions as BlockDefinition[]).forEach(def => this.register(def));
    }

    /**
     * Register a new block definition
     * @param definition 
     */
    public register(definition: BlockDefinition): void {
        if (!definition.name) {
            logger.error('Block definition must have a name');
            return;
        }
        this.blocks.value.set(definition.name, definition);
    }

    /**
     * Get a block definition by name
     * @param name 
     * @returns 
     */
    public get(name: string): BlockDefinition | undefined {
        return this.blocks.value.get(name);
    }

    /**
     * Get all registered blocks as an array
     * @returns 
     */
    public getAll(): BlockDefinition[] {
        return Array.from(this.blocks.value.values());
    }

    /**
     * Get block component for rendering
     * @param name 
     */
    public getComponent(name: string): Component | undefined {
        const block = this.get(name);
        return block?.component as Component;
    }
}

// Export a singleton instance
export const blockRegistry = new BlockRegistry();
