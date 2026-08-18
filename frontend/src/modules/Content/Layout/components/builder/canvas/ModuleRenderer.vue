<template>
  <component 
    v-if="BlockComponent"
    :is="BlockComponent"
    :module="module"
    :is-preview="true"
    :mode="'edit'"
    :device="currentDevice"
  >
    <!-- Pass children through slot -->
    <slot />
  </component>
  <div v-else class="module-placeholder">
    <span>{{ module.type }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import ModuleRegistry from '@/components/builder/core/ModuleRegistry'
import type { BlockInstance, BuilderInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
}>()

const builder = inject<BuilderInstance>('builder')
const currentDevice = computed(() => builder?.device.value || 'desktop')

const BlockComponent = computed(() => {
  return ModuleRegistry.getComponent(props.module.type)
})
</script>

<style scoped>
.module-placeholder {
  padding: var(--spacing-lg);
  background: var(--builder-bg-tertiary);
  border-radius: var(--border-radius-sm);
  text-align: center;
  color: var(--builder-text-muted);
  font-size: var(--font-size-sm);
}
</style>
