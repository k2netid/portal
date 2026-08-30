<template>
  <component 
    v-if="BlockComponent"
    :is="BlockComponent"
    :module="module"
    :is-preview="true"
    :mode="'edit'"
    :device="currentDevice"
  >
    <slot />
  </component>

  <!-- 1. Structure Container: Section -->
  <section
    v-else-if="module.type === 'section' || module.type === 'fullwidth_section'"
    class="builder-canvas-section w-full transition-all"
    :class="[
      getSettingStr(module, 'css_class'),
      (getSettingBool(module, 'fullWidth') || getSettingBool(module, 'fullwidth')) ? 'w-full' : 'container mx-auto px-4'
    ]"
    :style="resolveBlockStyles(module)"
  >
    <slot />
  </section>

  <!-- 2. Structure Container: Row -->
  <div
    v-else-if="module.type === 'row'"
    class="builder-canvas-row w-full"
    :class="[getSettingStr(module, 'css_class')]"
    :style="resolveBlockStyles(module)"
  >
    <slot />
  </div>

  <!-- 3. Structure Container: Column -->
  <div
    v-else-if="module.type === 'column'"
    class="builder-canvas-column flex flex-col space-y-4 w-full h-full min-h-[50px]"
    :class="getSettingStr(module, 'css_class')"
    :style="resolveBlockStyles(module)"
  >
    <slot />
  </div>

  <!-- 4. WYSIWYG Content Blocks -->
  <BlockRenderer
    v-else
    :block="module"
    mode="edit"
    :is-preview="true"
  />
</template>

<script setup lang="ts">
import { computed, inject } from 'vue';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import type { BlockInstance, BuilderInstance } from '@/modules/Layout/types/builder';

defineProps<{
  module: BlockInstance;
}>();

const builder = inject<BuilderInstance>('builder');
const currentDevice = computed(() => builder?.device?.value || 'desktop');

// Leaves always go through BlockRenderer (same as /site). Structure shells stay here for DnD slots.
const BlockComponent = computed(() => null)

const getSettingStr = (block: BlockInstance, key: string, fallback = ''): string => {
  const val = block.settings?.[key];
  if (typeof val === 'string') return val;
  if (typeof val === 'number') return String(val);
  return fallback;
};

const getSettingBool = (block: BlockInstance, key: string, fallback = false): boolean => {
  const val = block.settings?.[key];
  if (typeof val === 'boolean') return val;
  return fallback;
};

const resolveBlockStyles = (block: BlockInstance): Record<string, string> => {
  const styles: Record<string, string> = {};
  const settings = block.settings || {};

  // Background
  if (typeof settings.background === 'object' && settings.background !== null) {
    const bg = settings.background as Record<string, any>;
    if (bg.color) styles.backgroundColor = bg.color;
    if (bg.gradient) styles.backgroundImage = bg.gradient;
  } else if (typeof settings.background === 'string') {
    styles.background = settings.background;
  }
  if (settings.background_color && typeof settings.background_color === 'string') {
    styles.backgroundColor = settings.background_color;
  }

  // Padding
  if (typeof settings.padding === 'object' && settings.padding !== null) {
    const p = settings.padding as Record<string, any>;
    if (p.top) styles.paddingTop = `${p.top}px`;
    if (p.bottom) styles.paddingBottom = `${p.bottom}px`;
    if (p.left) styles.paddingLeft = `${p.left}px`;
    if (p.right) styles.paddingRight = `${p.right}px`;
  }

  // Margin
  if (typeof settings.margin === 'object' && settings.margin !== null) {
    const m = settings.margin as Record<string, any>;
    if (m.top) styles.marginTop = `${m.top}px`;
    if (m.bottom) styles.marginBottom = `${m.bottom}px`;
    if (m.left) styles.marginLeft = m.left === 'auto' ? 'auto' : `${m.left}px`;
    if (m.right) styles.marginRight = m.right === 'auto' ? 'auto' : `${m.right}px`;
  }

  return styles;
};
</script>
