<template>
    <div class="block-renderer w-full h-full">
        <template v-for="blockInstance in internalBlocks" :key="blockInstance.id">
            <!-- Visibility Condition Check -->
            <template v-if="ConditionEvaluator.evaluate(blockInstance, context)">
                <component 
                    :is="getBlockComponent(blockInstance.type)" 
                    v-bind="resolveBlockSettings(blockInstance)"
                    :context="context"
                    :is-preview="isPreview"
                    :mode="mode"
                    :id="blockInstance.id"
                />
            </template>
            <!-- Hidden placeholder for builder mode is handled by BaseBlock styles or can be re-added if needed for strictly hidden elements, 
                 but Visibility logic is now in BaseBlock. -->
        </template>
    </div>
</template>

<script setup lang="ts">
import { computed, provide, getCurrentInstance, type Component } from 'vue';
import { blockRegistry } from './BlockRegistry';
import { dynamicContent } from '@/services/DynamicContentService';
import { ConditionEvaluator, type RenderingContext } from '@/services/ConditionEvaluator';
import SanitizationService from '@/shared/utils/SanitizationService';
import type { BlockInstance } from '@/types/builder';

const props = defineProps<{
    blocks?: BlockInstance[];
    // Support single block for recursion
    block?: BlockInstance | null;
    context?: RenderingContext;
    isPreview?: boolean;
    // Pass mode for shared blocks
    mode?: 'view' | 'edit';
}>();

const internalBlocks = computed<BlockInstance[]>(() => {
    if (props.block) return [props.block];
    return props.blocks || [];
});

const instance = getCurrentInstance();
if (instance) {
    // Provide this component for recursion by name or type
    provide('BlockRenderer', instance.type);
}

const resolveBlockSettings = (block: BlockInstance) => {
    // Simplified: Pass module, settings, and children.
    // Legacy prop flattening is removed as we expect blocks to use BaseBlock or access module/settings directly.
    
    const settings = { ...block.settings };
    
    // Process dynamic settings
    const dynamicSettings = block.dynamicSettings;
    if (dynamicSettings) {
        Object.entries(dynamicSettings).forEach(([key, sourceId]) => {
            if (sourceId) {
                const resolvedValue = dynamicContent.resolve(sourceId as string, props.context || {});
                if (resolvedValue !== null && resolvedValue !== undefined) {
                    settings[key] = resolvedValue;
                }
            }
        });
    }

    // 2. Wrap resolved settings in a security layer
    // We sanitize the key functional fields to prevent XSS.
    const sanitizedSettings = SanitizationService.sanitizeObject(settings);
    
    return {
        module: block,
        settings: sanitizedSettings,
        nestedBlocks: block.children || []
    };
};

const getBlockComponent = (type: string): Component | undefined => {
    return blockRegistry.getComponent(type);
};
</script>
